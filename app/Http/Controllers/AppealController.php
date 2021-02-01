<?php

namespace App\Http\Controllers;

use App\Models\Appeal;
use App\Models\LogEntry;
use App\Models\Old\Oldappeal;
use App\Models\Old\Olduser;
use App\Models\Privatedata;
use App\Models\Template;
use App\Models\User;
use App\Services\Facades\MediaWikiRepository;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AppealController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('appeal');
    }

    public function appeal(Request $request, $id)
    {
        if (!Auth::check()) {
            if ($request->has('send_to_oauth')) {
                // fancy tricks to set intended path as cookie, but without the GET param
                $redirect = redirect();
                $redirect->setIntendedUrl(app(UrlGenerator::class)->previous());
                return $redirect->route('login');
            }

            return response()->view('appeals.public.needauth', [], 401);
        }

        $info = Appeal::find($id);
        /** @var User $user */
        $user = Auth::user();

        // UTRS 2 appeal exists
        if ($info) {
            $this->authorize('view', $info);
            $isDeveloper = $user->hasAnySpecifiedLocalOrGlobalPerms([], 'developer');

            /** @var User $user */
            $user = Auth::user();

            $logs = $info->comments;

            $cudata = Privatedata::where('appealID', '=', $id)->get()->first();

            $perms = [];
            $perms['checkuser'] = $user->can('viewCheckUserInformation', $info);
            $perms['functionary'] = $perms['checkuser'] || $user->hasAnySpecifiedLocalOrGlobalPerms($info->wiki, 'oversight');
            $perms['admin'] = $user->hasAnySpecifiedLocalOrGlobalPerms($info->wiki, 'admin');
            $perms['tooladmin'] = $user->hasAnySpecifiedLocalOrGlobalPerms($info->wiki, 'tooladmin');
            $perms['developer'] = $isDeveloper;

            $checkuserdone = $info->comments()
                ->where('user_id', Auth::id())
                ->where('action', 'checkuser')
                ->exists();

            $previousAppeals = Appeal::where('wiki', $info->wiki)
                ->where(function ($query) use ($info) {
                    $query->where('appealfor', $info->appealfor)
                        ->orWhere('hiddenip', $info->appealfor);
                })
                ->where('id', '!=', $info->id)
                ->where('status', '!=', Appeal::STATUS_INVALID)
                ->where('status', '!=', Appeal::STATUS_NOTFOUND)
                ->with('handlingAdminObject')
                ->orderByDesc('id')
                ->get();

            return view('appeals.appeal', [
                'id' => $id,
                'info' => $info,
                'comments' => $logs,
                'cudata' => $cudata,
                'checkuserdone' => $checkuserdone,
                'perms' => $perms,
                'previousAppeals' => $previousAppeals,
            ]);
        }

        // if UTRS 1 table exists, let's check there as well
        if (Schema::hasTable('oldappeals')) {
            $info = Oldappeal::findOrFail($id);
            $this->authorize('view', $info);

            $comments = $info->comments;
            $userlist = [];

            foreach ($comments as $comment) {
                if (!is_null($comment->commentUser) && !in_array($comment->commentUser, $userlist)) {
                    $userlist[$comment->commentUser] = $comment->commentUser === 0
                        ? 'System'
                        : Olduser::findOrFail($comment->commentUser)->username;
                }
            }


            if ($info['status'] === "UNVERIFIED") {
                return view('appeals.unverifiedappeal');
            }

            return view('appeals.oldappeal', ['info' => $info, 'comments' => $comments, 'userlist' => $userlist]);
        }

        throw (new ModelNotFoundException)->setModel(Appeal::class, $id);
    }

    public function appeallist()
    {
        abort_unless(Auth::check(), 403, 'No logged in user');
        /** @var User $user */
        $user = Auth::user();

        $isDeveloper = $user->hasAnySpecifiedPermsOnAnyWiki('developer');
        $isTooladmin = $isDeveloper || $user->hasAnySpecifiedPermsOnAnyWiki('tooladmin');
        $isCUAnyWiki = $isDeveloper || $user->hasAnySpecifiedPermsOnAnyWiki('checkuser');

        $wikis = collect(MediaWikiRepository::getSupportedTargets());

        // For users who aren't developers, stewards or staff, show appeals only for own wikis
        if (!$isDeveloper && !$user->hasAnySpecifiedLocalOrGlobalPerms(['*'], ['steward', 'staff'])) {
            $wikis = $wikis
                ->filter(function ($wiki) use ($user) {
                    $neededPermissions = MediaWikiRepository::getWikiPermissionHandler($wiki)
                        ->getRequiredGroupsForAction('appeal_view');
                    return $user->hasAnySpecifiedLocalOrGlobalPerms($wiki, $neededPermissions);
                });
        }

        $appealtypes = ['assigned'=>'Assigned to me','unassigned'=>'All unreserved open appeals','reserved'=>'Open reserved appeals'];
        if($isDeveloper) { $appealtypes['developer']='Developer access appeals'; }

        $developerStatuses = [Appeal::STATUS_VERIFY, Appeal::STATUS_NOTFOUND];
        $basicStatuses = [Appeal::STATUS_ACCEPT, Appeal::STATUS_DECLINE, Appeal::STATUS_EXPIRE, Appeal::STATUS_VERIFY, Appeal::STATUS_NOTFOUND, Appeal::STATUS_INVALID, Appeal::STATUS_CHECKUSER];

        $appeals[$appealtypes['assigned']] = Appeal::whereIn('wiki', $wikis)->where(function ($query) use ($basicStatuses) {
            $query->whereNotIn('status', $basicStatuses)
            ->where('handlingadmin',Auth::id());
        })->orWhere(function ($query) use ($isCUAnyWiki) {
            if ($isCUAnyWiki) {
                $query->where('status',Appeal::STATUS_CHECKUSER);
            }
        })
            ->get();
        $appeals[$appealtypes['unassigned']] = Appeal::whereIn('wiki', $wikis)
            ->whereNotIn('status', $basicStatuses)
            ->where(function ($query) {
            $query->where('handlingadmin','!=',Auth::id())
            ->orWhereNull('handlingadmin');
        })->get();
        $appeals[$appealtypes['reserved']] = Appeal::whereIn('wiki', $wikis)
            ->whereNotIn('status', $basicStatuses)
            ->where(function ($query) use ($isCUAnyWiki) {
                if ($isCUAnyWiki) {
                    $query->where('handlingadmin','!=',Auth::id());
                }
                else {
                    $query->where('handlingadmin','!=',Auth::id())
                        ->orWhere('status',Appeal::STATUS_CHECKUSER);   
                }
            })->get();
        if($isDeveloper) {
            $appeals[$appealtypes['developer']] = Appeal::whereIn('status',$developerStatuses)
            ->get();
        }

        return view('appeals.appeallist', ['appeals' => $appeals, 'appealtypes' => $appealtypes, 'tooladmin' => $isTooladmin, 'noWikis' => $wikis->isEmpty()]);
    }

    public function search(Request $request)
    {
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
        if (!$user->hasAnySpecifiedLocalOrGlobalPerms(['*'], ['steward', 'staff', 'developer'])) {
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

        return redirect()
            ->to('/appeal/' . $appeal->id);
    }

    public function accountappeal()
    {
        if (Auth::id() !== null) {
            return view('appeals.loggedin');
        }
        return view('appeals.makeappeal.account');
    }

    public function ipappeal()
    {
        if (Auth::id() !== null) {
            return view('appeals.loggedin');
        }
        return view('appeals.makeappeal.ip');
    }

    public function checkuser(Appeal $appeal, Request $request)
    {
        $this->authorize('viewCheckUserInformation', $appeal);

        $user = Auth::id();

        $ua = $request->userAgent();
        $ip = $request->ip();
        $lang = $request->header('Accept-Language');

        $reason = $request->input('reason');

        LogEntry::create(['user_id' => $user, 'model_id' => $appeal->id, 'model_type' => Appeal::class, 'action' => 'checkuser', 'reason' => $reason, 'ip' => $ip, 'ua' => $ua . " " . $lang, 'protected' => LogEntry::LOG_PROTECTION_FUNCTIONARY]);
        return redirect()->route('appeal.view', $appeal);
    }
  
    public function comment(Request $request, Appeal $appeal)
    {
        $this->authorize('update', $appeal);

        $ua = $request->userAgent();
        $ip = $request->ip();
        $lang = $request->header('Accept-Language');
        $reason = $request->input('comment');

        LogEntry::create(array('user_id' => $request->user()->id, 'model_id' => $appeal->id, 'model_type' => Appeal::class, 'action' => 'comment', 'reason' => $reason, 'ip' => $ip, 'ua' => $ua . " " . $lang, 'protected' => LogEntry::LOG_PROTECTION_ADMIN));
        return redirect()->route('appeal.view', $appeal);
    }

    public function respond(Request $request, Appeal $appeal, Template $template)
    {
        $this->authorize('update', $appeal);
        $user = $request->user();

        abort_unless($appeal->handlingadmin === $user->id, 403, 'You are not the handling administrator.');

        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');

        $status = $request->validate([
            'status' => ['nullable', Rule::in(Appeal::REPLY_STATUS_CHANGE_OPTIONS)],
        ])['status'];

        if ($status && $status !== $appeal->status) {
            $appeal->update([
                'status' => $status,
            ]);

            LogEntry::create([
                'user_id' => $user->id,
                'model_id' => $appeal->id,
                'model_type' => Appeal::class,
                'action' => 'set status as ' . $status,
                'ip' => $ip,
                'ua' => $ua . ' ' . $lang,
                'protected' => LogEntry::LOG_PROTECTION_NONE,
            ]);
        }

        LogEntry::create([
            'user_id' => $user->id,
            'model_id' => $appeal->id,
            'model_type' => Appeal::class,
            'action' => 'responded',
            'reason' => $template->template,
            'ip' => $ip,
            'ua' => $ua . " " . $lang,
            'protected' => LogEntry::LOG_PROTECTION_NONE,
        ]);

        return redirect()->route('appeal.view', $appeal);
    }

    public function respondCustomSubmit(Request $request, Appeal $appeal)
    {
        $this->authorize('update', $appeal);
        $user = $request->user();

        abort_unless($appeal->handlingadmin === $user->id, 403, 'You are not the handling administrator.');

        $status = $request->validate([
            'status' => ['nullable', Rule::in(Appeal::REPLY_STATUS_CHANGE_OPTIONS)],
        ])['status'];

        $ua = $request->userAgent();
        $ip = $request->ip();
        $lang = $request->header('Accept-Language');

        if ($status && $status !== $appeal->status) {
            $appeal->update([
                'status' => $status,
            ]);

            LogEntry::create([
                'user_id' => $user->id,
                'model_id' => $appeal->id,
                'model_type' => Appeal::class,
                'action' => 'set status as ' . $status,
                'ip' => $ip,
                'ua' => $ua . ' ' . $lang,
                'protected' => LogEntry::LOG_PROTECTION_NONE,
            ]);
        }

        LogEntry::create([
            'user_id' => $user->id,
            'model_id' => $appeal->id,
            'model_type' => Appeal::class,
            'action' => 'responded',
            'reason' => $request->input('custom'),
            'ip' => $ip,
            'ua' => $ua . " " . $lang,
            'protected' => LogEntry::LOG_PROTECTION_NONE,
        ]);

        return redirect()->route('appeal.view', $appeal);
    }

    public function viewtemplates(Request $request, Appeal $appeal)
    {
        $this->authorize('update', $appeal);

        $templates = Template::where('active', '=', 1)->get();
        return view('appeals.templates', ['templates' => $templates, 'appeal' => $appeal, 'username' => $request->user()->username]);
    }

    public function respondCustom(Appeal $appeal)
    {
        $this->authorize('update', $appeal);

        $userlist = [];
        $userlist[Auth::id()] = Auth::user()->username;

        return view('appeals.custom', ['appeal' => $appeal, 'userlist' => $userlist]);
    }

    public function checkuserreview(Request $request, Appeal $appeal)
    {
        $this->authorize('update', $appeal);

        $ua = $request->userAgent();
        $ip = $request->ip();
        $lang = $request->header('Accept-Language');
        $user = $request->user();

        abort_if($appeal->status !== Appeal::STATUS_OPEN, 403, 'Appeal is in invalid state');

        $reason = $request->validate([
            'cu_reason' => 'required|string|min:3|max:190',
        ])['cu_reason'];

        $appeal->status = Appeal::STATUS_CHECKUSER;
        $appeal->save();

        LogEntry::create([
            'user_id' => $user,
            'model_id' => $appeal->id,
            'model_type' => Appeal::class,
            'action' => 'sent for checkuser review',
            'reason' => $reason,
            'ip' => $ip,
            'ua' => $ua . ' ' . $lang,
            'protected' => LogEntry::LOG_PROTECTION_ADMIN,
        ]);

        return redirect()->back();
    }
}
