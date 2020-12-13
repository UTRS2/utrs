<?php

namespace App\Http\Controllers\Appeal;

use App\Http\Controllers\Controller;
use App\Models\Appeal;
use App\Models\User;
use App\Services\Facades\MediaWikiRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AppealAdvancedSearchController extends Controller
{
    const ALL_BLOCK_TYPES_WITH_NAMES = [
        Appeal::BLOCKTYPE_IP => 'IP address',
        Appeal::BLOCKTYPE_ACCOUNT => 'Named account',
        Appeal::BLOCKTYPE_IP_UNDER_ACCOUNT => 'IP under account',
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function search(Request $request)
    {
        $this->authorize('viewAny', Appeal::class);

        /** @var User $user */
        $user = $request->user();

        $filled = $request->has('dosearch');

        $wikiInputs = collect(MediaWikiRepository::getSupportedTargets())
            ->filter(function ($wiki) use ($user) {
                $neededPermissions = MediaWikiRepository::getWikiPermissionHandler($wiki)
                    ->getRequiredGroupsForAction('appeal_view');
                return $user->hasAnySpecifiedLocalOrGlobalPerms($wiki, $neededPermissions);
            })
            ->mapWithKeys(function ($wiki) use ($request, $filled) {
                return [$wiki => $request->get('wiki_'.$wiki, !$filled)];
            });

        $statusInputs = collect(Appeal::ALL_STATUSES)
            ->filter(function ($status) use ($user) {
                if ($user->hasAnySpecifiedLocalOrGlobalPerms([], 'developer')) {
                    return true;
                }

                return !in_array($status, Appeal::REGULAR_NO_VIEW_STATUS);
            })
            ->mapWithKeys(function ($status) use ($request, $filled) {
                return [$status => $request->get('status_' . $status, !$filled)];
            });

        $blockTypeInputs = collect(array_keys(self::ALL_BLOCK_TYPES_WITH_NAMES))
            ->mapWithKeys(function ($blockType) use ($request, $filled) {
                return [$blockType => $request->get('blocktype_'.$blockType, !$filled)];
            });

        $results = null;
        if ($filled) {
            $wikisToSearch = $wikiInputs->filter()->keys();
            $statusesToSearch = $statusInputs->filter()->keys();
            $blockTypesToSearch = $blockTypeInputs->filter()->keys();

            $results = $this->doRunSearch(
                $request,
                $wikisToSearch,
                $statusesToSearch,
                $blockTypesToSearch,
            );
        }

        return view('appeals.search.search', [
            'hasResults' => $filled,
            'results' => $results,
            'blockTypeNames' => self::ALL_BLOCK_TYPES_WITH_NAMES,

            'wikiInputs' => $wikiInputs,
            'statusInputs' => $statusInputs,
            'blockTypeInputs' => $blockTypeInputs,
        ]);
    }

    private function doRunSearch(Request $request, Collection $wikisToSearch, Collection $statusesToSearch,
                                 Collection $blockTypesToSearch)
    {
        return Appeal::whereIn('wiki', $wikisToSearch)
            ->whereIn('status', $statusesToSearch)
            ->whereIn('blocktype', $blockTypesToSearch)
            ->when($request->input('appealfor'), function (Builder $query, $value) {
                $query->where('appealfor', 'LIKE', $value);
            })
            ->when($request->input('blockingadmin'), function (Builder $query, $value) {
                $query->where('blockingadmin', $value);
            })
            ->when($request->input('handlingadmin'), function (Builder $query, $value) {
                $query->whereHas('handlingAdminObject', function (Builder $adminQuery) use ($value) {
                    $adminQuery->where('username', $value);
                });
            })
            ->when($request->input('handlingadmin_none'), function (Builder $query) {
                $query->whereNull('handlingadmin');
            })
            ->get();
    }
}
