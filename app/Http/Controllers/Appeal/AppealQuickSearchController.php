<?php

namespace App\Http\Controllers\Appeal;

use App\Http\Controllers\Controller;
use App\Models\Appeal;
use App\Models\Old\Oldappeal;
use App\Models\User;
use App\Services\Facades\MediaWikiRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AppealQuickSearchController extends Controller
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
        
        $search = $request->validate(['search' => 'required|min:1'])['search'];

        $number = is_numeric($search) ? intval($search) : null;

        // if search starts with a "#" and is followed by numbers, it should be treated as number
        if (!$number && Str::startsWith($search, '#') && is_numeric(substr($search, 1))) {
            $number = intval(substr($search, 1), 10);
        }

        $wikis = collect(MediaWikiRepository::getSupportedTargets(true));

        // For users who aren't developers, stewards or staff, show appeals only for own wikis
        if (!$user->hasAnySpecifiedLocalOrGlobalPerms(['global'], ['steward', 'staff', 'developer'])) {
            $wikis = $wikis
                ->filter(function ($wiki) use ($user) {
                    return $user->hasAnySpecifiedLocalOrGlobalPerms($wiki, 'admin');
                });
        }

        $appeal = Appeal::where('appealfor', $search)
            ->when($number, function (Builder $query, $number) {
                return $query->orWhere('id', $number);
            })
            ->whereIn('wiki', $wikis)
            ->orderByDesc('id')
            ->first();

        // for enwiki admins,
        // try to find an UTRS 1 appeal if no UTRS 2 appeals were found
        if (!$appeal && $wikis->contains('enwiki') && Schema::hasTable('oldappeals')) {
            $appeal = Oldappeal::where(function (Builder $query) use ($search) {
                return $query->where('hasAccount', true)
                    ->where('wikiAccountName', $search);
            })
                ->orWhere(function (Builder $query) use ($search) {
                    return $query->where('hasAccount', false)
                        ->where('ip', $search);
                })
                ->when($number, function (Builder $query, $number) {
                    return $query->orWhere('appealID', $number);
                })
                ->orderByDesc('appealID')
                ->first();
        }

        // If no appeals were found at all, show error message
        if (!$appeal) {
            return redirect()
                ->back(302, [], route('appeal.list'))
                ->withErrors([
                    'search' => 'No results found.'
                ]);
        }

        if (!$user->can('view', $appeal)) {
            return redirect()
                ->back(302, [], route('appeal.list'))
                ->withErrors([
                    'search' => 'You are not allowed to view that appeal.'
                ]);
        }

        return redirect()->route('appeal.view', $appeal);
    }
}
