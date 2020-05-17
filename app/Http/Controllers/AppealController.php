<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App;
use App\Appeal;
use App\Oldappeal;
use App\Olduser;
use App\User;
use App\Privatedata;
use App\Permission;
use App\Ban;
use App\Log;
use App\Template;
use App\Sendresponse;
use Auth;
use Validator;
use Redirect;
use Illuminate\Support\Arr;

class AppealController extends Controller
{
    public function appeal($id) {
        Auth::user()->checkRead();

        $info = Appeal::find($id);
        if (is_null($info)) {
            $info = Oldappeal::find($id);
            abort_if(is_null($info), 404,'Appeal does not exist or you do not have access to it.');

            //Enwiki is hardcoded here as all previous appeals were only on enwiki.
            //Since that had a different policy at the time, we have to still observe the same privacy level.
            $perms['admin'] = Permission::checkAdmin(Auth::id(),'enwiki');
            abort_if(is_null($info), 403,'Non-English Wikipedia administrators do not have access to appeals made in UTRS 1.');            

            $comments = $info->comments()->get();
            $userlist = [];

            foreach($comments as $comment) {
                if (!is_null($comment->commentUser) && !in_array($comment->commentUser, $userlist)) {
                    $userlist[$comment->commentUser] = Olduser::findOrFail($comment->commentUser)->username;
                }
            }

            if ($info['status'] === "UNVERIFIED") {
                return view('appeals.unverifiedappeal');
            }

            return view('appeals.oldappeal', ['info' => $info, 'comments' => $comments, 'userlist'=>$userlist]);
        } else {
            $isDeveloper = Permission::checkSecurity(Auth::id(), "DEVELOPER","*");

            $closestatus = ($info->status=="ACCEPT" || $info->status=="DECLINE" || $info->status=="EXPIRE");
            abort_if($info->status == "INVALID" && !$isDeveloper, 404,'This appeal has been marked invalid.');

            if (($info->status == "OPEN" || $info->status == "PRIVACY" || $info->status == "ADMIN" || $info->status == "CHECKUSER" || $closestatus) || $isDeveloper) {
                $logs = $info->comments()->get();
                $userlist = [];

                if (!is_null($info->handlingadmin)) {
                    $userlist[$info->handlingadmin] = User::findOrFail($info->handlingadmin)['username'];
                }

                $cudata = Privatedata::where('appealID','=',$id)->get()->first();

                $perms['checkuser'] = Permission::checkCheckuser(Auth::id(),$info->wiki);
                $perms['functionary'] = $perms['checkuser'] || Permission::checkOversight(Auth::id(),$info->wiki);
                $perms['admin'] = Permission::checkAdmin(Auth::id(),$info->wiki);
                $perms['tooladmin'] = Permission::checkToolAdmin(Auth::id(),$info->wiki);
                $perms['dev'] = $isDeveloper;

                $replies = Sendresponse::where('appealID','=',$id)->where('custom','!=','null')->get();
                $checkuserdone = !is_null(Log::where('user','=',Auth::id())->where('action','=','checkuser')->where('referenceobject','=',$id)->first());

                if ($info->privacyreview !== $info->privacylevel || $info->privacylevel == 2) {
                    if (!Permission::checkPrivacy(Auth::id(), $info->wiki) && !Permission::checkOversight(Auth::id(), $info->wiki)) {
                        return view('appeals.privacydeny');
                    }
                }

                if ($info->privacylevel == 1 && !$perms['admin']) {
                    return view('appeals.privacydeny');
                }

                foreach($logs as $log) {
                    if (is_null($log->user) || $log->user==0 || in_array($log->user, $userlist)) {
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
                    ->where('status', '!=','INVALID')
                    ->where('status', '!=','NOTFOUND')
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
                return view ('appeals.deny');
            }
        }
    }

    public function publicappeal(Request $request) {
        $hash = $request->input('hash');
        $info = Appeal::where('appealsecretkey','=',$hash)->firstOrFail();
        $closestatus = $info->status=="ACCEPT" || $info->status=="DECLINE" || $info->status=="EXPIRE";

        $id = $info->id;
        $logs = $info->comments()->get();
        $userlist = [];
        if (!is_null($info->handlingadmin)) {
            $userlist[$info->handlingadmin] = User::findOrFail($info->handlingadmin)['username'];
        }
        $replies = Sendresponse::where('appealID','=',$id)->where('custom','!=','null')->get();
        foreach($logs as $log) {
            if(is_null($log->user) || $log->user==0) {continue;}
            if(in_array($log->user, $userlist)) {continue;}
            $userlist[$log->user] = User::findOrFail($log->user)['username'];
        }
        return view('appeals.publicappeal', ['id'=>$id,'info' => $info, 'comments' => $logs, 'userlist'=>$userlist, 'replies'=>$replies,'hash'=>$hash]);
    }

    public function publicComment(Request $request) {
        $key = $request->input('appealsecretkey');
        $appeal = Appeal::where('appealsecretkey', $key)->firstOrFail();

        abort_if($appeal->status=="ACCEPT" || $appeal->status=="DECLINE" || $appeal->status=="EXPIRE", 400, "Appeal is closed");

        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $reason = $request->input('comment');

        Log::create([
            'user' => 0,
            'referenceobject' => $appeal->id,
            'objecttype' => 'appeal',
            'action' => 'comment',
            'reason' => $reason,
            'ip' => $ip,
            'ua' => $ua . " " .$lang,
            'protected' => 0
        ]);

        return redirect()->back();
    }

    public function appeallist() {
        $regularnoview = ["ACCEPT", "DECLINE", "EXPIRE", "VERIFY", "PRIVACY","NOTFOUND","INVALID"];
        $privacynoview = ["ACCEPT", "DECLINE", "EXPIRE", "VERIFY","NOTFOUND","INVALID"];
        $devnoview = ["ACCEPT", "DECLINE", "EXPIRE", "INVALID"];
        $tooladmin = False;
        if (!Auth::check()) {
            abort(403,'No logged in user');
        }
        User::findOrFail(Auth::id())->checkRead();
        if (Auth::user()['wikis']=="*") {
            $wikis = ["*"];
        } else {
            $wikis = explode(",",(Auth::user()['wikis']));
        }
        foreach ($wikis as $wiki) {
            if (Permission::checkToolAdmin(Auth::id(),$wiki)) {$tooladmin=True;}
            if(Permission::checkSecurity(Auth::id(),"DEVELOPER","*")) {
                $appeals = Appeal::whereNotIn('status',$devnoview)->get();
            }
            elseif (Permission::checkPrivacy(Auth::id(),$wiki) && Auth::user()['wikis'] != "*") {
                $appeals = Appeal::where('wiki','=',$wiki)->whereNotIn('status',$privacynoview)->get();
            }
            elseif (Permission::checkPrivacy(Auth::id(),$wiki)) {
                $appeals = Appeal::whereNotIn('status',$privacynoview)->get();
            }
            elseif (Auth::user()['wikis'] == "*") {
                $appeals = Appeal::whereNotIn('status',$regularnoview)->get();
            }
            else {
                $appeals = Appeal::where('wiki','=',$wiki)->whereNotIn('status',$regularnoview)->get();
            }
        }
        return view ('appeals.appeallist', ['appeals'=>$appeals, 'tooladmin'=>$tooladmin]);
    }
    public function accountappeal() {
        if (Auth::id() !== null) {
            return view ('appeals.loggedin');
        }
        return view ('appeals.makeappeal.account');
    }
    public function appealsubmit(Request $request) {
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $input = $request->all();
        Arr::forget($input, '_token');
        $input = Arr::add($input, 'status', 'VERIFY');
        $key = hash('md5', $ip.$ua.$lang.date("Ymd"));
        $input = Arr::add($input, 'appealsecretkey', $key);
        
        $request->validate([
            'appealtext' => 'max:4000|required',
            'appealfor' => 'required',
            'wiki' => 'required',
            'blocktype' => 'required|numeric|max:2|min:0',
            'privacyreview' => 'required|numeric|max:2|min:0'
        ]);

        if (Appeal::where('appealfor','=',$input['appealfor'])->where('status','!=','ACCEPT')->where('status','!=','EXPIRE')->where('status','!=','DECLINE')->count() > 0 || sizeof(Appeal::where('appealsecretkey')->get())>0) {
            return view('appeals.spam');
        }

        $appealbyname = Appeal::where('appealfor','=',$input['appealfor'])->orderBy('id', 'desc')->first();
        if (!is_null($appealbyname)) {
            $lastdate = $appealbyname['submitted'];
            $now = date('Y-m-d H:i:s');
            $interval = strtotime($now)-strtotime($lastdate);
            if ($interval < 172800) {
                return view('appeals.spam');
            }  
        }
        $banacct = Ban::where('target','=',$input['appealfor'])->first(); 
        $banip = Ban::where('target','=',$ip)->first();
        if(!is_null($banacct)) {
            return view('appeals.ban', ['expire'=>$banacct->expiry,'id'=>$banacct['id']]);
        }
        if(!is_null($banip)) {
            return view('appeals.ban', ['expire'=>$banip['expiry'],'id'=>$banip['id']]);
        }
        $appeal = Appeal::create($input);
        $cudata = Privatedata::create(array('appealID' => $appeal->id,'ipaddress' => $ip, 'useragent' => $ua, 'language' => $lang));
        $log = Log::create(array('user' => 0, 'referenceobject'=>$appeal['id'],'objecttype'=>'appeal','action'=>'create','ip' => $ip, 'ua' => $ua . " " .$lang));
        return view ('appeals.makeappeal.hash', ['hash'=>$key]);
    }
    public function ipappeal() {
        if (Auth::id() !== null) {
            return view ('appeals.loggedin');
        }
        return view ('appeals.makeappeal.ip');
    }
    public function checkuser($id, Request $request) {
        if (!Auth::check()) {
            $response->assertUnauthorized();
        }
        User::findOrFail(Auth::id())->checkRead();
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $reason = $request->input('reason');
        $checkuser = Permission::checkCheckuser($user,$appeal->wiki);
        if ($checkuser) {
            $log = Log::create(array('user' => $user, 'referenceobject'=>$id,'objecttype'=>'appeal','action'=>'checkuser','reason'=>$reason,'ip' => $ip, 'ua' => $ua . " " .$lang, 'protected'=>1));
            return redirect('appeal/'.$id);
        }
        else {
            $response->assertUnauthorized();
        }
    }
    public function comment($id, Request $request) {
        if (!Auth::check()) {
            $response->assertUnauthorized();
        }
        User::findOrFail(Auth::id())->checkRead();
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $reason = $request->input('comment');
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $checkuser = Permission::checkAdmin($user,$appeal->wiki);
        $log = Log::create(array('user' => $user, 'referenceobject'=>$id,'objecttype'=>'appeal','action'=>'comment','reason'=>$reason,'ip' => $ip, 'ua' => $ua . " " .$lang, 'protected'=>0));
        return redirect('appeal/'.$id);
    }
    public function respond($id, $template, Request $request) {
        if (!Auth::check()) {
            abort(403,'No logged in user');
        }
        User::findOrFail(Auth::id())->checkRead();
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $admin = Permission::checkAdmin($user,$appeal->wiki);
        $templateObject = Template::find($template);
        $text = $templateObject->template;
        if ($admin && $appeal->handlingadmin==Auth::id()) {
            $mail = Sendresponse::create(array('appealID'=>$id, 'template'=>$template));
            $log = Log::create(array('user' => $user, 'referenceobject'=>$id,'objecttype'=>'appeal','action'=>'responded','reason'=>$text,'ip' => $ip, 'ua' => $ua . " " .$lang, 'protected'=>0));
            return redirect('appeal/'.$id);
        }
        else {
            abort(403);
        }
    }
    public function respondCustomSubmit($id, Request $request) {
        if (!Auth::check()) {
            abort(403,'No logged in user');
        }
        User::findOrFail(Auth::id())->checkRead();
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $admin = Permission::checkAdmin($user,$appeal->wiki);
        if ($admin && $appeal->handlingadmin==Auth::id()) {
            $mail = Sendresponse::create(array('appealID'=>$id, 'template'=>0, 'custom'=>$request->input('custom')));
            $log = Log::create(array('user' => $user, 'referenceobject'=>$id,'objecttype'=>'appeal','action'=>'responded','reason'=>$request->input('custom'),'ip' => $ip, 'ua' => $ua . " " .$lang, 'protected'=>0));
            return redirect('appeal/'.$id);
        }
        else {
            abort(403);
        }
    }
    public function viewtemplates($id) {
        if (!Auth::check()) {
            $response->assertUnauthorized();
        }
        User::findOrFail(Auth::id())->checkRead();
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $admin = Permission::checkAdmin($user,$appeal->wiki);
        $userlist = [];
        $userlist[Auth::id()] = User::findOrFail(Auth::id())['username'];
        if ($admin) {
            $templates = Template::where('active','=',1)->get();
            return view ('appeals.templates', ['templates'=>$templates,'appeal'=>$appeal, 'userlist'=>$userlist]);
        }
        else {
            $response->assertUnauthorized();
        }
    }
    public function respondCustom($id) {
        if (!Auth::check()) {
            $response->assertUnauthorized();
        }
        User::findOrFail(Auth::id())->checkRead();
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $admin = Permission::checkAdmin($user,$appeal->wiki);
        $userlist = [];
        $userlist[Auth::id()] = User::findOrFail(Auth::id())['username'];
        if ($admin) {
            return view ('appeals.custom', ['appeal'=>$appeal, 'userlist'=>$userlist]);
        }
        else {
            $response->assertUnauthorized();
        }
    }
    public function reserve($id, Request $request) {
        if (!Auth::check()) {
            abort(403,'No logged in user');
        }
        User::findOrFail(Auth::id())->checkRead();
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $admin = Permission::checkAdmin($user,$appeal->wiki);
        if ($admin) {
            if(!isset($appeal->handlingadmin)) {
                $appeal->handlingadmin = Auth::id();
                $appeal->save();
                $log = Log::create(array('user' => $user, 'referenceobject'=>$id,'objecttype'=>'appeal','action'=>'reserve','ip' => $ip, 'ua' => $ua . " " .$lang, 'protected'=>0));
            }
            else {
                abort(403);
            }
            return redirect('appeal/'.$id);
        }
        else {
            abort(403);
        }
    }
    public function release($id, Request $request) {
        if (!Auth::check()) {
            abort(403,'No logged in user');
        }
        User::findOrFail(Auth::id())->checkRead();
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $admin = Permission::checkAdmin($user,$appeal->wiki);
        if ($admin) {
            if(isset($appeal->handlingadmin)) {
                $appeal->handlingadmin = null;
                $appeal->save();
                $log = Log::create(array('user' => $user, 'referenceobject'=>$id,'objecttype'=>'appeal','action'=>'release','ip' => $ip, 'ua' => $ua . " " .$lang, 'protected'=>0));
            }
            else {
                abort(403);
            }
            return redirect('appeal/'.$id);
        }
        else {
            abort(403);
        }
    }
    public function open($id, Request $request) {
        if (!Auth::check()) {
            abort(403,'No logged in user');
        }
        User::findOrFail(Auth::id())->checkRead();
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $tooladmin = Permission::checkCheckuser($user,$appeal->wiki) || Permission::checkOversight($user,$appeal->wiki);
        if ($tooladmin) {
            if($appeal->status=="ACCEPT" || $appeal->status=="EXPIRE" || $appeal->status=="DECLINE" || $appeal->status=="CHECKUSER" || $appeal->status=="ADMIN") {
                $appeal->status = "OPEN";
                $appeal->save();
                $log = Log::create(array('user' => $user, 'referenceobject'=>$id,'objecttype'=>'appeal','action'=>'re-open','ip' => $ip, 'ua' => $ua . " " .$lang, 'protected'=>0));
            }
            else {
                abort(403);
            }
            return redirect('appeal/'.$id);
        }
        else {
            abort(403);
        }
    }
    public function invalidate($id, Request $request) {
        if (!Auth::check()) {
            abort(403,'No logged in user');
        }
        User::findOrFail(Auth::id())->checkRead();
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $dev = Permission::checkSecurity($user,"DEVELOPER",$appeal->wiki);
        if ($dev && $appeal->status!=="INVALID") {
            $appeal->status = "INVALID";
            $appeal->save();
            $log = Log::create(array('user' => $user, 'referenceobject'=>$id,'objecttype'=>'appeal','action'=>'closed - invalidate','ip' => $ip, 'ua' => $ua . " " .$lang, 'protected'=>0));
            return redirect('appeal/'.$id);
        }
        else {
            abort(403);
        }
    }
    public function close($id, $type, Request $request) {
        if (!Auth::check()) {
            abort(403,'No logged in user');
        }
        User::findOrFail(Auth::id())->checkRead();
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $admin = Permission::checkAdmin($user,$appeal->wiki);
        if ($admin) {
            $appeal->status = strtoupper($type);
            $appeal->save();
            $log = Log::create(array('user' => $user, 'referenceobject'=>$id,'objecttype'=>'appeal','action'=>'closed - '.$type,'ip' => $ip, 'ua' => $ua . " " .$lang, 'protected'=>0));
            return redirect('/review');
        }
        else {
            abort(403);
        }
    }
    public function privacy($id, Request $request) {
        if (!Auth::check()) {
            abort(403,'No logged in user');
        }
        User::findOrFail(Auth::id())->checkRead();
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $admin = Permission::checkAdmin($user,$appeal->wiki);
        if ($admin && $appeal->status!=="PRIVACY") {
            $appeal->status = "PRIVACY";
            $appeal->privacyreview = 2;
            $appeal->save();
            $log = Log::create(array('user' => $user, 'referenceobject'=>$id,'objecttype'=>'appeal','action'=>'sent for privacy review','ip' => $ip, 'ua' => $ua . " " .$lang, 'protected'=>0));
            return redirect('/review');
        }
        else {
            abort(403);
        }
    }
    public function checkuserreview($id, Request $request) {
        if (!Auth::check()) {
            abort(403,'No logged in user');
        }
        User::findOrFail(Auth::id())->checkRead();
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $admin = Permission::checkAdmin($user,$appeal->wiki);
        if ($admin && $appeal->status!=="CHECKUSER") {
            $appeal->status = "CHECKUSER";
            $appeal->save();
            $log = Log::create(array('user' => $user, 'referenceobject'=>$id,'objecttype'=>'appeal','action'=>'sent for checkuser review','ip' => $ip, 'ua' => $ua . " " .$lang, 'protected'=>0));
            return redirect('appeal/'.$id);
        }
        else {
            abort(403);
        }
    }
    public function admin($id, Request $request) {
        if (!Auth::check()) {
            abort(403,'No logged in user');
        }
        User::findOrFail(Auth::id())->checkRead();
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $admin = Permission::checkAdmin($user,$appeal->wiki);
        if ($admin && $appeal->status!=="ADMIN") {
            $appeal->status = "ADMIN";
            $appeal->save();
            $log = Log::create(array('user' => $user, 'referenceobject'=>$id,'objecttype'=>'appeal','action'=>'sent for admin review','ip' => $ip, 'ua' => $ua . " " .$lang, 'protected'=>0));
            return redirect('appeal/'.$id);
        }
        else {
            abort(403);
        }
    }
    public function privacyhandle(Request $request,$id,$action) {
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $appeal = Appeal::findOrFail($id);
        $user = Auth::id();
        if (Permission::checkPrivacy(Auth::id(),$appeal->wiki) || Permission::checkOversight(Auth::id(),$info->wiki)) {
            if ($action == "publicize") {
                $appeal->privacyreview = 0;
                $appeal->privacylevel = 0;
                $appeal->status = "OPEN";
                $appeal->save();
                $log = Log::create(array('user' => $user, 'referenceobject'=>$id,'objecttype'=>'appeal','action'=>'publicized','ip' => $ip, 'ua' => $ua . " " .$lang, 'protected'=>0));
            }
            if ($action == "privatize") {
                $appeal->privacyreview = 1;
                $appeal->privacylevel = 1;
                $appeal->status = "OPEN";
                $appeal->save();
                $log = Log::create(array('user' => $user, 'referenceobject'=>$id,'objecttype'=>'appeal','action'=>'privatized','ip' => $ip, 'ua' => $ua . " " .$lang, 'protected'=>0));
            }
            if ($action == "oversight") {
                $appeal->privacyreview = 2;
                $appeal->privacylevel = 2;
                $appeal->status = "OPEN";
                $appeal->save();
                $log = Log::create(array('user' => $user, 'referenceobject'=>$id,'objecttype'=>'appeal','action'=>'oversighted','ip' => $ip, 'ua' => $ua . " " .$lang, 'protected'=>0));
            }
            return redirect('appeal/'.$id);
        }
        else {abort(401);}
        return view('appeals.publicappeal', ['id'=>$id,'info' => $info, 'comments' => $logs, 'userlist'=>$userlist, 'replies'=>$replies]);
    }
}
