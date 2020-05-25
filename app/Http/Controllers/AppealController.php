<?php

namespace App\Http\Controllers;

use App;
use App\Appeal;
use App\Log;
use App\Oldappeal;
use App\Olduser;
use App\Permission;
use App\Privatedata;
use App\Sendresponse;
use App\Template;
use App\User;
use Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\GetBlockDetailsJob;
use Illuminate\Database\Eloquent\Builder;

class AppealController extends Controller
{
    public function appeal($id)
    {
        Auth::user()->checkRead();

        $info = Appeal::find($id);
        if (is_null($info)) {
            $info = Oldappeal::find($id);
            abort_if(is_null($info), 404, 'Appeal does not exist or you do not have access to it.');

            //Enwiki is hardcoded here as all previous appeals were only on enwiki.
            //Since that had a different policy at the time, we have to still observe the same privacy level.
            $isAdmin = Permission::checkAdmin(Auth::id(), 'enwiki');
            abort_unless($isAdmin, 403, 'You are not an administrator on the wiki this appeal is for.');

            $comments = $info->comments()->get();
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
            $isDeveloper = Permission::checkSecurity(Auth::id(), "DEVELOPER", "*");
            User::findOrFail(Auth::id())->checkRead();
            $appeal = Appeal::findOrFail($id);
            $user = Auth::id();
            $admin = Permission::checkAdmin($user, $appeal->wiki);
            abort_unless($admin,403,"You are not an administrator on the wiki this appeal is for.");

            $closestatus = ($info->status == "ACCEPT" || $info->status == "DECLINE" || $info->status == "EXPIRE");
            abort_if($info->status == "INVALID" && !$isDeveloper, 404, 'This appeal has been marked invalid.');

            if (($info->status == "OPEN" || $info->status == "ADMIN" || $info->status == "CHECKUSER" || $closestatus) || $isDeveloper) {
                $logs = $info->comments()->get();
                $userlist = [];

                if (!is_null($info->handlingadmin)) {
                    $userlist[$info->handlingadmin] = User::findOrFail($info->handlingadmin)['username'];
                }

                $cudata = Privatedata::where('appealID', '=', $id)->get()->first();

                $perms['checkuser'] = Permission::checkCheckuser(Auth::id(), $info->wiki);
                $perms['functionary'] = $perms['checkuser'] || Permission::checkOversight(Auth::id(), $info->wiki);
                $perms['admin'] = Permission::checkAdmin(Auth::id(), $info->wiki);
                $perms['tooladmin'] = Permission::checkToolAdmin(Auth::id(), $info->wiki);
                $perms['developer'] = boolval($isDeveloper);

                $replies = Sendresponse::where('appealID', '=', $id)->where('custom', '!=', 'null')->get();
                $checkuserdone = !is_null(Log::where('user', '=', Auth::id())->where('action', '=', 'checkuser')->where('referenceobject', '=', $id)->first());

                foreach($logs as $log) {
                    if (is_null($log->user) || $log->user === 0 || $log->user === -1 || in_array($log->user, $userlist)) {
                        continue;
                    }

                    $userlist[$log->user] = User::findOrFail($log->user)->username;
                }

                $previousAppeals = Appeal::where('wiki', $info->wiki)
                    ->where(function ($query) use ($info) {
                        $query->where('appealfor', $info->appealfor)
                            ->orWhere('hiddenip', $info->appealfor);
                    })
                    ->where('id', '!=', $info->id)
                    ->where('status', '!=', 'INVALID')
                    ->where('status', '!=', 'NOTFOUND')
                    ->with('handlingAdminObject')
                    ->orderByDesc('id')
                    ->get();

                return view('appeals.appeal', [
                    'id' => $id,
                    'info' => $info,
                    'comments' => $logs,
                    'userlist' => $userlist,
                    'cudata' => $cudata,
                    'checkuserdone' => $checkuserdone,
                    'perms' => $perms,
                    'replies' => $replies,
                    'previousAppeals' => $previousAppeals,
                ]);
            } else {
                return view('appeals.deny');
            }
        }
    }

    public function appeallist()
    {
        $regularnoview = ["ACCEPT", "DECLINE", "EXPIRE", "VERIFY", "NOTFOUND", "INVALID"];
        $devnoview = ["ACCEPT", "DECLINE", "EXPIRE", "INVALID"];
        $tooladmin = False;
        if (!Auth::check()) {
            abort(403, 'No logged in user');
        }
        User::findOrFail(Auth::id())->checkRead();
        if (Auth::user()['wikis'] == "*") {
            $wikis = ["*"];
        } else {
            $wikis = explode(",", (Auth::user()['wikis']));
        }
        foreach ($wikis as $wiki) {
            if (Permission::checkToolAdmin(Auth::id(), $wiki)) {
                $tooladmin = true;
            }
            if (Permission::checkSecurity(Auth::id(), "DEVELOPER", "*")) {
                $appeals = Appeal::whereNotIn('status', $devnoview)->get();
            } elseif (Auth::user()['wikis'] == "*") {
                $appeals = Appeal::whereNotIn('status', $regularnoview)->get();
            } else {
                $appeals = Appeal::where('wiki', '=', $wiki)->whereNotIn('status', $regularnoview)->get();
            }
        }
        return view('appeals.appeallist', ['appeals' => $appeals, 'tooladmin' => $tooladmin]);
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

    public function checkuser($id, Request $request)
    {
        if (!Auth::check()) {
            abort(403, 'No logged in user');
        }
        User::findOrFail(Auth::id())->checkRead();
        $appeal = Appeal::findOrFail($id);
        $user = Auth::id();
        $admin = Permission::checkAdmin($user, $appeal->wiki);
        abort_if(!$admin,403,"You are not an administrator on the wiki this appeal is for");
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $reason = $request->input('reason');
        $checkuser = Permission::checkCheckuser($user, $appeal->wiki);
        if (!$checkuser) {
            abort(403, 'You are not a checkuser.');
        }

        Log::create(['user' => $user, 'referenceobject' => $id, 'objecttype' => 'appeal', 'action' => 'checkuser', 'reason' => $reason, 'ip' => $ip, 'ua' => $ua . " " . $lang, 'protected' => 1]);
        return redirect('appeal/' . $id);
    }

    public function comment($id, Request $request)
    {
        if (!Auth::check()) {
            abort(403, 'No logged in user');
        }
        Auth::user()->checkRead();
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
        $log = Log::create(array('user' => $user, 'referenceobject' => $id, 'objecttype' => 'appeal', 'action' => 'comment', 'reason' => $reason, 'ip' => $ip, 'ua' => $ua . " " . $lang, 'protected' => 0));
        return redirect('appeal/' . $id);
    }

    public function respond($id, $template, Request $request)
    {
        if (!Auth::check()) {
            abort(403, 'No logged in user');
        }
        Auth::user()->checkRead();
        $appeal = Appeal::findOrFail($id);
        $user = Auth::id();
        $admin = Permission::checkAdmin($user, $appeal->wiki);
        abort_if(!$admin,403,"You are not an administrator on the wiki this appeal is for");
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        
        
        $templateObject = Template::find($template);
        $text = $templateObject->template;
        if ($admin && $appeal->handlingadmin == Auth::id()) {
            $mail = Sendresponse::create(array('appealID' => $id, 'template' => $template));
            $log = Log::create(array('user' => $user, 'referenceobject' => $id, 'objecttype' => 'appeal', 'action' => 'responded', 'reason' => $text, 'ip' => $ip, 'ua' => $ua . " " . $lang, 'protected' => 0));
            return redirect('appeal/' . $id);
        } else {
            abort(403);
        }
    }

    public function respondCustomSubmit($id, Request $request)
    {
        if (!Auth::check()) {
            abort(403, 'No logged in user');
        }
        Auth::user()->checkRead();
        $appeal = Appeal::findOrFail($id);
        $user = Auth::id();
        $admin = Permission::checkAdmin($user, $appeal->wiki);
        abort_if(!$admin,403,"You are not an administrator on the wiki this appeal is for");
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        if ($admin && $appeal->handlingadmin == Auth::id()) {
            $mail = Sendresponse::create(array('appealID' => $id, 'template' => 0, 'custom' => $request->input('custom')));
            $log = Log::create(array('user' => $user, 'referenceobject' => $id, 'objecttype' => 'appeal', 'action' => 'responded', 'reason' => $request->input('custom'), 'ip' => $ip, 'ua' => $ua . " " . $lang, 'protected' => 0));
            return redirect('appeal/' . $id);
        } else {
            abort(403);
        }
    }

    public function viewtemplates($id)
    {
        if (!Auth::check()) {
            abort(403, 'No logged in user');
        }

        Auth::user()->checkRead();
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $admin = Permission::checkAdmin($user, $appeal->wiki);
        abort_unless($admin,403, 'You are not an administrator.');

        $userlist = [];
        $userlist[Auth::id()] = Auth::user()->username;

        $templates = Template::where('active', '=', 1)->get();
        return view('appeals.templates', ['templates' => $templates, 'appeal' => $appeal, 'userlist' => $userlist]);
    }

    public function respondCustom($id)
    {
        if (!Auth::check()) {
            abort(403, 'No logged in user');
        }
        User::findOrFail(Auth::id())->checkRead();
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $admin = Permission::checkAdmin($user, $appeal->wiki);
        abort_unless($admin,403, 'You are not an administrator.');

        $userlist = [];
        $userlist[Auth::id()] = Auth::user()->username;

        return view('appeals.custom', ['appeal' => $appeal, 'userlist' => $userlist]);
    }

    public function reserve($id, Request $request)
    {
        if (!Auth::check()) {
            abort(403, 'No logged in user');
        }
        User::findOrFail(Auth::id())->checkRead();
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $admin = Permission::checkAdmin($user, $appeal->wiki);
        abort_unless($admin,403, 'You are not an administrator.');
        abort_if($appeal->handlingadmin, 403, 'This appeal has already been reserved.');
        $appeal->handlingadmin = Auth::id();
        $appeal->save();
        Log::create(['user' => $user, 'referenceobject' => $id, 'objecttype' => 'appeal', 'action' => 'reserve', 'ip' => $ip, 'ua' => $ua . " " . $lang, 'protected' => 0]);

        return redirect('appeal/' . $id);
    }

    public function release($id, Request $request)
    {
        if (!Auth::check()) {
            abort(403, 'No logged in user');
        }
        User::findOrFail(Auth::id())->checkRead();
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
                $log = Log::create(array('user' => $user, 'referenceobject' => $id, 'objecttype' => 'appeal', 'action' => 'release', 'ip' => $ip, 'ua' => $ua . " " . $lang, 'protected' => 0));
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
        if (!Auth::check()) {
            abort(403, 'No logged in user');
        }
        Auth::user()->checkRead();
        $appeal = Appeal::findOrFail($id);
        $user = Auth::id();
        $admin = Permission::checkAdmin($user, $appeal->wiki);
        abort_if(!$admin,403,"You are not an administrator on the wiki this appeal is for");
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $user = Auth::id();
        if ($admin) {
            if ($appeal->status == "ACCEPT" || $appeal->status == "EXPIRE" || $appeal->status == "DECLINE" || $appeal->status == "CHECKUSER" || $appeal->status == "ADMIN") {
                $appeal->status = "OPEN";
                $appeal->save();
                $log = Log::create(array('user' => $user, 'referenceobject' => $id, 'objecttype' => 'appeal', 'action' => 're-open', 'ip' => $ip, 'ua' => $ua . " " . $lang, 'protected' => 0));
            } else {
                abort(403);
            }
            return redirect('appeal/' . $id);
        } else {
            abort(403);
        }
    }

    public function invalidate($id, Request $request)
    {
        if (!Auth::check()) {
            abort(403, 'No logged in user');
        }
        User::findOrFail(Auth::id())->checkRead();
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $dev = Permission::checkSecurity($user, "DEVELOPER", $appeal->wiki);
        if ($dev && $appeal->status !== "INVALID") {
            $appeal->status = "INVALID";
            $appeal->save();
            $log = Log::create(array('user' => $user, 'referenceobject' => $id, 'objecttype' => 'appeal', 'action' => 'closed - invalidate', 'ip' => $ip, 'ua' => $ua . " " . $lang, 'protected' => 0));
            return redirect('appeal/' . $id);
        } else {
            abort(403);
        }
    }

    public function close($id, $type, Request $request)
    {
        if (!Auth::check()) {
            abort(403, 'No logged in user');
        }
        User::findOrFail(Auth::id())->checkRead();
        Auth::user()->checkRead();
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
            $log = Log::create(array('user' => $user, 'referenceobject' => $id, 'objecttype' => 'appeal', 'action' => 'closed - ' . $type, 'ip' => $ip, 'ua' => $ua . " " . $lang, 'protected' => 0));
            return redirect('/review');
        } else {
            abort(403);
        }
    }

    public function checkuserreview($id, Request $request)
    {
        if (!Auth::check()) {
            abort(403, 'No logged in user');
        }
        Auth::user()->checkRead();
        $appeal = Appeal::findOrFail($id);
        $user = Auth::id();
        $admin = Permission::checkAdmin($user, $appeal->wiki);
        abort_if(!$admin,403,"You are not an administrator on the wiki this appeal is for");
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        if ($admin && $appeal->status !== "CHECKUSER") {
            $appeal->status = "CHECKUSER";
            $appeal->save();
            $log = Log::create(array('user' => $user, 'referenceobject' => $id, 'objecttype' => 'appeal', 'action' => 'sent for checkuser review', 'ip' => $ip, 'ua' => $ua . " " . $lang, 'protected' => 0));
            return redirect('appeal/' . $id);
        } else {
            abort(403);
        }
    }

    public function admin($id, Request $request)
    {
        if (!Auth::check()) {
            abort(403, 'No logged in user');
        }
        Auth::user()->checkRead();
        $appeal = Appeal::findOrFail($id);
        $user = Auth::id();
        $admin = Permission::checkAdmin($user, $appeal->wiki);
        abort_if(!$admin,403,"You are not an administrator on the wiki this appeal is for");
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        if ($admin && $appeal->status !== "ADMIN") {
            $appeal->status = "ADMIN";
            $appeal->save();
            $log = Log::create(array('user' => $user, 'referenceobject' => $id, 'objecttype' => 'appeal', 'action' => 'sent for admin review', 'ip' => $ip, 'ua' => $ua . " " . $lang, 'protected' => 0));
            return redirect('appeal/' . $id);
        } else {
            abort(403);
        }
    }

    public function findagain($id, Request $request)
    {
        if (!Auth::check()) {
            abort(403, 'No logged in user');
        }
        User::findOrFail(Auth::id())->checkRead();
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');

        $dev = Permission::checkSecurity($user, "DEVELOPER", $appeal->wiki);
        if ($dev && ($appeal->status == "NOTFOUND" || $appeal->status == "VERIFY")) {
            GetBlockDetailsJob::dispatch($appeal);
            Log::create([
                'user' => Auth::id(),
                'referenceobject'=> $appeal->id,
                'objecttype'=>'appeal',
                'action'=>'reverify block',
                'ip' => $ip,
                'ua' => $ua . " " .$lang
            ]);

        } else {
            abort(403,'Not developer/Not NOTFOUND');
        }
        return redirect('appeal/' . $id);
    }
}
