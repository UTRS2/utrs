<?php

namespace App\Http\Controllers;

use App\Appeal;
use App\Log;
use App\MwApi\MwApiUrls;
use App\Oldappeal;
use App\Olduser;
use App\Permission;
use App\Privatedata;
use App\Sendresponse;
use App\Template;
use App\User;
use Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class AppealController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('appeal');
    }

    public function appeal($id)
    {
        $info = Appeal::find($id);
        if (is_null($info)) {
            $info = Oldappeal::findOrFail($id);
            $this->authorize('view', $info);

            $comments = $info->comments;
            $userlist = [];

            foreach ($comments as $comment) {
                if (!is_null($comment->commentUser) && !in_array($comment->commentUser, $userlist)) {
                    $userlist[$comment->commentUser] = Olduser::findOrFail($comment->commentUser)->username;
                }
            }

            if ($info['status'] === "UNVERIFIED") {
                return view('appeals.unverifiedappeal');
            }

            return view('appeals.oldappeal', ['info' => $info, 'comments' => $comments, 'userlist' => $userlist]);
        } else {
            $this->authorize('view', $info);
            $isDeveloper = Permission::checkSecurity(Auth::id(), "DEVELOPER","*");

            $logs = $info->comments;

            $cudata = Privatedata::where('appealID', '=', $id)->get()->first();

            $perms = [];
            $perms['checkuser'] = Permission::checkCheckuser(Auth::id(), $info->wiki);
            $perms['functionary'] = $perms['checkuser'] || Permission::checkOversight(Auth::id(), $info->wiki);
            $perms['admin'] = Permission::checkAdmin(Auth::id(), $info->wiki);
            $perms['tooladmin'] = Permission::checkToolAdmin(Auth::id(), $info->wiki);
            $perms['developer'] = $isDeveloper;

            $replies = Sendresponse::where('appealID', '=', $id)->where('custom', '!=', 'null')->get();
            $checkuserdone = $info->comments()
                ->where('user', Auth::id())
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
                'replies' => $replies,
                'previousAppeals' => $previousAppeals,
            ]);
        }
    }

    public function appeallist()
    {
        abort_unless(Auth::check(), 403, 'No logged in user');
        /** @var User $user */
        $user = Auth::user();

        $isDeveloper = $user->hasAnySpecifiedPermsOnAnyWiki('developer');
        $isTooladmin = $isDeveloper || $user->hasAnySpecifiedPermsOnAnyWiki('tooladmin');
        $isCUAnyWiki = $isDeveloper || $user->hasAnySpecifiedPermsOnAnyWiki('checkuser');

        if ($user->wikis === '*' || $isDeveloper || $user->hasAnySpecifiedLocalOrGlobalPerms(['*'], ['steward', 'staff'])) {
            $wikis = collect(MwApiUrls::getSupportedWikis())
                ->push('global');
        } else {
            $wikis = collect(explode(',', $user->wikis ?? ''))
                ->filter(function ($wiki) use ($user) {
                    return $user->hasAnySpecifiedLocalOrGlobalPerms($wiki, 'admin');
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

        return view('appeals.appeallist', ['appeals' => $appeals, 'appealtypes'=>$appealtypes, 'tooladmin' => $isTooladmin]);
    }

    public function search(Request $request)
    {
        $search = $request->validate(['search' => 'required|min:1'])['search'];

        $number = is_numeric($search) ? intval($search) : null;

        // if search starts with a "#" and is followed by numbers, it should be treated as number
        if (!$number && Str::startsWith($search, '#') && is_numeric(substr($search, 1))) {
            $number = intval(substr($search, 1), 10);
        }

        $appeal = Appeal::where('appealfor', $search)
            ->when($number, function (Builder $query, $number) {
                return $query->orWhere('id', $number);
            })
            ->orderByDesc('id')
            ->first();
        
        // try to find an UTRS 1 appeal if no UTRS 2 appeals were found
        if (!$appeal && Schema::hasTable('oldappeals')) {
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

    public function comment($id, Request $request)
    {
        if (!Auth::check()) {
            abort(403, 'No logged in user');
        }

        $appeal = Appeal::findOrFail($id);
        $user = Auth::id();
        $admin = Permission::checkAdmin($user, $appeal->wiki);
        abort_if(!$admin,403,"You are not an administrator on the wiki this appeal is for");
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $reason = $request->input('comment');
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $checkuser = Permission::checkAdmin($user, $appeal->wiki);
        $log = Log::create(array('user' => $user, 'referenceobject' => $id, 'objecttype' => 'appeal', 'action' => 'comment', 'reason' => $reason, 'ip' => $ip, 'ua' => $ua . " " . $lang, 'protected' => Log::LOG_PROTECTION_ADMIN));
        return redirect('appeal/' . $id);
    }

    public function respond(Request $request, Appeal $appeal, Template $template)
    {
        $user = Auth::id();
        $admin = Permission::checkAdmin($user, $appeal->wiki);
        abort_unless($admin, 403, 'You are not an administrator on the wiki this appeal is for');
        abort_unless($appeal->handlingadmin === $user, 403, 'You are not the handling administrator.');

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

            Log::create([
                'user' => $user,
                'referenceobject' => $appeal->id,
                'objecttype' => 'appeal',
                'action' => 'set status as ' . $status,
                'ip' => $ip,
                'ua' => $ua . ' ' . $lang,
                'protected' => Log::LOG_PROTECTION_NONE,
            ]);
        }

        Sendresponse::create(['appealID' => $appeal->id, 'template' => $template->id]);
        Log::create([
            'user' => $user,
            'referenceobject' => $appeal->id,
            'objecttype' => 'appeal',
            'action' => 'responded',
            'reason' => $template->template,
            'ip' => $ip,
            'ua' => $ua . " " . $lang,
            'protected' => Log::LOG_PROTECTION_NONE,
        ]);

        return redirect('appeal/' . $appeal->id);
    }

    public function respondCustomSubmit(Request $request, Appeal $appeal)
    {
        $user = Auth::id();
        $admin = Permission::checkAdmin($user, $appeal->wiki);
        abort_unless($admin, 403, 'You are not an administrator on the wiki this appeal is for');
        abort_unless($appeal->handlingadmin === $user, 403, 'You are not the handling administrator.');

        $status = $request->validate([
            'status' => ['nullable', Rule::in(Appeal::REPLY_STATUS_CHANGE_OPTIONS)],
        ])['status'];

        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');

        if ($status && $status !== $appeal->status) {
            $appeal->update([
                'status' => $status,
            ]);

            Log::create([
                'user' => $user,
                'referenceobject' => $appeal->id,
                'objecttype' => 'appeal',
                'action' => 'set status as ' . $status,
                'ip' => $ip,
                'ua' => $ua . ' ' . $lang,
                'protected' => Log::LOG_PROTECTION_NONE,
            ]);
        }

        Sendresponse::create([
            'appealID' => $appeal->id,
            'template' => 0,
            'custom' => $request->input('custom'),
        ]);

        Log::create([
            'user' => $user,
            'referenceobject' => $appeal->id,
            'objecttype' => 'appeal',
            'action' => 'responded',
            'reason' => $request->input('custom'),
            'ip' => $ip,
            'ua' => $ua . " " . $lang,
            'protected' => Log::LOG_PROTECTION_NONE,
        ]);

        return redirect('appeal/' . $appeal->id);
    }

    public function viewtemplates(Appeal $appeal)
    {
        $user = Auth::user();
        $admin = Permission::checkAdmin($user->id, $appeal->wiki);
        abort_unless($admin,403, 'You are not an administrator.');

        $templates = Template::where('active', '=', 1)->get();
        return view('appeals.templates', ['templates' => $templates, 'appeal' => $appeal, 'username' => $user->username]);
    }

    public function respondCustom(Appeal $appeal)
    {
        $user = Auth::id();
        $admin = Permission::checkAdmin($user, $appeal->wiki);
        abort_unless($admin,403, 'You are not an administrator.');

        $userlist = [];
        $userlist[Auth::id()] = Auth::user()->username;

        return view('appeals.custom', ['appeal' => $appeal, 'userlist' => $userlist]);
    }
}
