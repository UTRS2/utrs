<?php

namespace App\Http\Controllers;

use App\Jobs\GetBlockDetailsJob;
use App\Models\Appeal;
use App\Models\LogEntry;
use App\Models\Old\Oldappeal;
use App\Models\Old\Olduser;
use App\Models\Permission;
use App\Models\Privatedata;
use App\Models\Sendresponse;
use App\Models\Template;
use App\Models\User;
use App\MwApi\MwApiUrls;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
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

        // UTRS 2 appeal exists
        if ($info) {
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
                'replies' => $replies,
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

    public function checkuser(Appeal $appeal, Request $request)
    {
        if (!Auth::check()) {
            abort(403, 'No logged in user');
        }

        $user = Auth::id();
        $admin = Permission::checkAdmin($user, $appeal->wiki);

        abort_if(!$admin,403,"You are not an administrator on the wiki this appeal is for");
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $user = Auth::id();

        $reason = $request->input('reason');
        $checkuser = Permission::checkCheckuser($user, $appeal->wiki);
        if (!$checkuser) {
            abort(403, 'You are not a checkuser.');
        }

        LogEntry::create(['user_id' => $user, 'model_id' => $appeal->id, 'model_type' => Appeal::class, 'action' => 'checkuser', 'reason' => $reason, 'ip' => $ip, 'ua' => $ua . " " . $lang, 'protected' => LogEntry::LOG_PROTECTION_FUNCTIONARY]);
        return redirect('appeal/' . $appeal->id);
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
        $log = LogEntry::create(array('user_id' => $user, 'model_id' => $id, 'model_type' => Appeal::class, 'action' => 'comment', 'reason' => $reason, 'ip' => $ip, 'ua' => $ua . " " . $lang, 'protected' => LogEntry::LOG_PROTECTION_ADMIN));
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

            LogEntry::create([
                'user_id' => $user,
                'model_id' => $appeal->id,
                'model_type' => Appeal::class,
                'action' => 'set status as ' . $status,
                'ip' => $ip,
                'ua' => $ua . ' ' . $lang,
                'protected' => LogEntry::LOG_PROTECTION_NONE,
            ]);
        }

        Sendresponse::create(['appealID' => $appeal->id, 'template' => $template->id]);
        LogEntry::create([
            'user_id' => $user,
            'model_id' => $appeal->id,
            'model_type' => Appeal::class,
            'action' => 'responded',
            'reason' => $template->template,
            'ip' => $ip,
            'ua' => $ua . " " . $lang,
            'protected' => LogEntry::LOG_PROTECTION_NONE,
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

            LogEntry::create([
                'user_id' => $user,
                'model_id' => $appeal->id,
                'model_type' => Appeal::class,
                'action' => 'set status as ' . $status,
                'ip' => $ip,
                'ua' => $ua . ' ' . $lang,
                'protected' => LogEntry::LOG_PROTECTION_NONE,
            ]);
        }

        Sendresponse::create([
            'appealID' => $appeal->id,
            'template' => 0,
            'custom' => $request->input('custom'),
        ]);

        LogEntry::create([
            'user_id' => $user,
            'model_id' => $appeal->id,
            'model_type' => Appeal::class,
            'action' => 'responded',
            'reason' => $request->input('custom'),
            'ip' => $ip,
            'ua' => $ua . " " . $lang,
            'protected' => LogEntry::LOG_PROTECTION_NONE,
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

    public function reserve(Appeal $appeal, Request $request)
    {
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $user = Auth::id();

        $admin = Permission::checkAdmin($user, $appeal->wiki);
        abort_unless($admin,403, 'You are not an administrator.');
        abort_if($appeal->handlingadmin, 403, 'This appeal has already been reserved.');
        $appeal->handlingadmin = Auth::id();
        $appeal->save();
        LogEntry::create(['user_id' => $user, 'model_id' => $appeal->id, 'model_type' => Appeal::class, 'action' => 'reserve', 'ip' => $ip, 'ua' => $ua . " " . $lang, 'protected' => LogEntry::LOG_PROTECTION_NONE]);

        return redirect('appeal/' . $appeal->id);
    }

    public function release($id, Request $request)
    {
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $admin = Permission::checkAdmin($user, $appeal->wiki);
        if ($admin) {
            if (isset($appeal->handlingadmin)) {
                $appeal->handlingadmin = null;
                $appeal->save();
                $log = LogEntry::create(array('user_id' => $user, 'model_id' => $id, 'model_type' => Appeal::class, 'action' => 'release', 'ip' => $ip, 'ua' => $ua . " " . $lang, 'protected' => LogEntry::LOG_PROTECTION_NONE));
            } else {
                abort(403);
            }
            return redirect('appeal/' . $id);
        } else {
            abort(403);
        }
    }

    public function open($id, Request $request)
    {
        $appeal = Appeal::findOrFail($id);
        $user = Auth::id();

        $admin = Permission::checkAdmin($user, $appeal->wiki);
        abort_if(!$admin,403,"You are not an administrator on the wiki this appeal is for");

        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $user = Auth::id();

        if ($appeal->status == Appeal::STATUS_ACCEPT || $appeal->status == Appeal::STATUS_EXPIRE || $appeal->status == Appeal::STATUS_DECLINE || $appeal->status == Appeal::STATUS_CHECKUSER || $appeal->status == Appeal::STATUS_ADMIN) {
            $appeal->status = Appeal::STATUS_OPEN;
            $appeal->save();
            LogEntry::create(array('user_id' => $user, 'model_id' => $id, 'model_type' => Appeal::class, 'action' => 're-open', 'ip' => $ip, 'ua' => $ua . " " . $lang, 'protected' => LogEntry::LOG_PROTECTION_NONE));
            return redirect('appeal/' . $id);
        } else {
            abort(403);
        }
}

    public function invalidate($id, Request $request)
    {
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $dev = Permission::checkSecurity($user, "DEVELOPER", "*");
        if ($dev && $appeal->status !== Appeal::STATUS_INVALID) {
            $appeal->status = Appeal::STATUS_INVALID;
            $appeal->save();
            $log = LogEntry::create(array('user_id' => $user, 'model_id' => $id, 'model_type' => Appeal::class, 'action' => 'closed - invalidate', 'ip' => $ip, 'ua' => $ua . " " . $lang, 'protected' => LogEntry::LOG_PROTECTION_ADMIN));
            return redirect('appeal/' . $id);
        } else {
            abort(403);
        }
    }

    public function close($id, $type, Request $request)
    {
        $appeal = Appeal::findOrFail($id);
        $user = Auth::id();
        $admin = Permission::checkAdmin($user, $appeal->wiki);
        abort_if(!$admin,403,"You are not an administrator on the wiki this appeal is for");
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        if ($admin) {
            $appeal->status = strtoupper($type);
            $appeal->save();
            $log = LogEntry::create(array('user_id' => $user, 'model_id' => $id, 'model_type' => Appeal::class, 'action' => 'closed - ' . $type, 'ip' => $ip, 'ua' => $ua . " " . $lang, 'protected' => LogEntry::LOG_PROTECTION_NONE));
            return redirect('/review');
        } else {
            abort(403);
        }
    }

    public function checkuserreview(Request $request, Appeal $appeal)
    {
        $ua = $request->header('User-Agent');
        $lang = $request->header('Accept-Language');
        $ip = $request->ip();
        $user = Auth::id();

        $admin = Permission::checkAdmin($user, $appeal->wiki);

        $reason = $request->validate([
            'cu_reason' => 'required|string|min:3|max:190',
        ])['cu_reason'];

        abort_unless($admin && $appeal->status == Appeal::STATUS_OPEN, 403, 'Forbidden');

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

    public function admin(Request $request, Appeal $appeal)
    {
        $user = Auth::id();
        $admin = Permission::checkAdmin($user, $appeal->wiki);
        abort_unless($admin,403,"You are not an administrator on the wiki this appeal is for");
        abort_if($appeal->status === Appeal::STATUS_ADMIN, 400, 'This appeal is already waiting for tool administrator review.');

        $ua = $request->userAgent();
        $ip = $request->ip();
        $lang = $request->header('Accept-Language');

        $appeal->status = Appeal::STATUS_ADMIN;
        $appeal->save();
        LogEntry::create([
            'user_id' => $user,
            'model_id' => $appeal->id,
            'model_type' => Appeal::class,
            'action' => 'sent for admin review',
            'ip' => $ip,
            'ua' => $ua . " " . $lang,
            'protected' => LogEntry::LOG_PROTECTION_NONE
        ]);

        return redirect()->back();
    }

    public function findagain(Request $request, Appeal $appeal)
    {
        /** @var User $user */
        $user = $request->user();

        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');

        $dev = $user->hasAnySpecifiedLocalOrGlobalPerms('*', 'developer');
        abort_unless($dev,403,"You are not an UTRS developer");
        abort_if($appeal->status !== Appeal::STATUS_NOTFOUND && $appeal->status !== Appeal::STATUS_VERIFY, 400, 'Appeal details were already found.');

        GetBlockDetailsJob::dispatch($appeal);
        LogEntry::create([
            'user_id' => Auth::id(),
            'model_id'=> $appeal->id,
            'model_type'=> Appeal::class,
            'action'=>'reverify block',
            'ip' => $ip,
            'ua' => $ua . " " .$lang
        ]);

        return redirect()->back();
    }
}
