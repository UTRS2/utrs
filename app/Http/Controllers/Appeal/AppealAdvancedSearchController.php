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

        $results = null;
        if ($filled) {
            $wikisToSearch = $wikiInputs->filter()->keys();
            $statusesToSearch = $statusInputs->filter()->keys();

            $results = $this->doRunSearch(
                $request,
                $wikisToSearch,
                $statusesToSearch,
            );

            // dd($results);
        }

        return view('appeals.search.search', [
            'hasResults' => $filled,
            'results' => $results,

            'wikiInputs' => $wikiInputs,
            'statusInputs' => $statusInputs,
        ]);
    }

    private function doRunSearch(Request $request, Collection $wikisToSearch, Collection $statusesToSearch)
    {
        return Appeal::whereIn('wiki', $wikisToSearch)
            ->whereIn('status', $statusesToSearch)
            ->when($request->input('blockingadmin'), function (Builder $query, $value) {
                $query->where('blockingadmin', $value);
            })
            ->when($request->input('handlingadmin'), function (Builder $query, $value) {
                $query->where('handlingadmin', $value);
            })
            ->when($request->input('handlingadmin_none'), function (Builder $query) {
                $query->whereNull('handlingadmin');
            })
            ->get();
    }
}
