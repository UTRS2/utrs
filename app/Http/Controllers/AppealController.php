<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
use App\Wikitask;
use Auth;
use Validator;
use Redirect;
use Illuminate\Support\Arr;

class AppealController extends Controller
{
    define("regularnoview", ["ACCEPT", "DECLINE", "EXPIRE","VERIFY","PRIVACY"]);
    define("privacynoview", ["ACCEPT", "DECLINE", "EXPIRE","VERIFY"]);
    define("devnoview", ["ACCEPT", "DECLINE", "EXPIRE"]);
    public function appeal($id) {
        if (!Auth::check()) {
            abort(403,'No logged in user');
        }
        User::findOrFail(Auth::id())->checkRead();
    	$info = Appeal::find($id);
    	if (is_null($info)) {
    		$info = Oldappeal::find($id);
            if (is_null($info)) {
                abort(404,'Appeal does not exist or you do not have access to it.');
            }
    		$comments = $info->comments()->get();
            $userlist = [];
            foreach($comments as $comment) {
                if(is_null($comment->commentUser)) {continue;}
                if(in_array($comment->commentUser, $userlist)) {continue;}
                $userlist[$comment->commentUser] = Olduser::findOrFail($comment->commentUser)['username'];
            }
    		if ($info['status'] === "UNVERIFIED") {
    			return view('appeals.unverifiedappeal');
    		}
    		return view('appeals.oldappeal', ['info' => $info, 'comments' => $comments, 'userlist'=>$userlist]);
    	}
    	else {
            if($info->status=="ACCEPT" || $info->status=="DECLINE" || $info->status=="EXPIRE") {$closestatus=TRUE;}
            else {$closestatus=FALSE;}
            if (($info->status !== "OPEN" || $info->status !== "PRIVACY" || $info->status !== "ADMIN" || $info->status !== "CHECKUSER" || $closestatus) || !Permission::checkSecurity($id, "DEVELOPER","*")) {
                $logs = $info->comments()->get();
                $userlist = [];
                if (!is_null($info->handlingadmin)) {
                    $userlist[$info->handlingadmin] = User::findOrFail($info->handlingadmin)['username'];
                }
                $cudata = Privatedata::where('appealID','=',$id)->get()->first();
                $perms['checkuser'] = Permission::checkCheckuser(Auth::id(),$info->wiki);
                $perms['functionary'] = Permission::checkCheckuser(Auth::id(),$info->wiki) || Permission::checkOversight(Auth::id(),$info->wiki);
                $perms['admin'] = Permission::checkAdmin(Auth::id(),$info->wiki);
                $perms['tooladmin'] = Permission::checkToolAdmin(Auth::id(),$info->wiki);
                $perms['dev'] = Permission::checkSecurity(Auth::id(),"DEVELOPER",$info->wiki);
                $replies = Sendresponse::where('appealID','=',$id)->where('custom','!=','null')->get();
                $checkuserdone = !is_null(Log::where('user','=',Auth::id())->where('action','=','checkuser')->where('referenceobject','=',$id)->first());
                foreach($logs as $log) {
                    if(is_null($log->user) || $log->user==0) {continue;}
                    if(in_array($log->user, $userlist)) {continue;}
                    $userlist[$log->user] = User::findOrFail($log->user)['username'];
                }
                if ($info->status == "PRIVACY") {
                    if (Permission::checkPrivacy(Auth::id())) {
                        return view('appeals.privacyreview', ['info' => $info, 'comments' => $logs, 'userlist'=>$userlist, 'cudata'=>$cudata, 'checkuserdone'=>$checkuserdone, 'perms'=>$perms, 'replies'=>$replies]);
                    }
                    else {
                        return view ('appeals.privacydeny');
                    }
                }
        		return view('appeals.appeal', ['id'=>$id,'info' => $info, 'comments' => $logs, 'userlist'=>$userlist, 'cudata'=>$cudata, 'checkuserdone'=>$checkuserdone, 'perms'=>$perms, 'replies'=>$replies]);	
            }
            else {
                return view ('appeals.deny');
            }
    	}
    }
    public function publicappeal(Request $request) {
        $input = $request->all();
        $hash = $input['hash'];
        $info = Appeal::where('appealsecretkey','=',$hash)->firstOrFail();
        if($info->status=="ACCEPT" || $info->status=="DECLINE" || $info->status=="EXPIRE") {$closestatus=TRUE;}
        else {$closestatus=FALSE;}
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
        return view('appeals.publicappeal', ['id'=>$id,'info' => $info, 'comments' => $logs, 'userlist'=>$userlist, 'replies'=>$replies]);
    }
    public function appeallist() {
        $tooladmin = False;
        if (!Auth::check()) {
            abort(403,'No logged in user');
        }
        User::findOrFail(Auth::id())->checkRead();
        if (Auth::user()['wikis']!=="*") {
            $wikis = ["*"];
        } else {
            $wikis = explode(",",(Auth::user()['wikis']));
        }
        foreach ($wikis as $wiki) {
            if (Permission::checkToolAdmin(Auth::id(),$wiki)) {$tooladmin=True;}
            if(Permission::checkSecurity(Auth::id(),"DEVELOPER","*")) {
                $appeals = Appeal::all();
                foreach (devnoview as $item) {
                    $appeals = $appeals->where('status','!=',$item)->get();
                }
            }
            elseif (Permission::checkPrivacy(Auth::id()) && Auth::user()['wikis'] != "*") {
                $appeals = Appeal::where('wiki','=',$wiki)->get();
                foreach (privacynoview as $item) {
                    $appeals = $appeals->where('status','!=',$item)->get();
                }
            }
            elseif (Permission::checkPrivacy(Auth::id())) {
                $appeals = Appeal::all();
                foreach (privacynoview as $item) {
                    $appeals = $appeals->where('status','!=',$item)->get();
                }
            }
            elseif (Auth::user()['wikis'] != "*") {
                $appeals = Appeal::all();
                foreach (regularnoview as $item) {
                    $appeals = $appeals->where('status','!=',$item)->get();
                }
            }
            else {
                $appeals = Appeal::where('wiki','=',$wiki)->get();
                foreach (regularnoview as $item) {
                    $appeals = $appeals->where('status','!=',$item)->get();
                }
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
    public function accountappealsubmit(Request $request) {
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->server('HTTP_X_FORWARDED_FOR');
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $input = $request->all();
        Arr::forget($input, '_token');
        $input = Arr::add($input, 'status', 'VERIFY');
        $key = hash('md5', $ip.$ua.$lang.date("Ymd"));
        $input = Arr::add($input, 'appealsecretkey', $key);
        $rules = array(
            'appealtext' => 'max:4000|required',
            'appealfor' => 'required',
            'wiki' => 'required',
            'blocktype' => 'required|numeric|max:2|min:0',
            'privacyreview' => 'required|numeric|max:2|min:0'
        );
        $validator = Validator::make($input, $rules);

        if ($validator->fails())
        {
            return Redirect::to('/appeal/account')->withInput()->withErrors($validator);
        }
        if (sizeof(Appeal::where('appealfor','=',$input['appealfor'])->where('status','!=','ACCEPT')->where('status','!=','EXPIRE')->where('status','!=','DECLINE')->get())>0 || sizeof(Appeal::where('appealsecretkey')->get())>0) {
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
        Wikitask::create(['task'=>'verifyblock','actionid'=>$appeal->id]);
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
        $ip = $request->server('HTTP_X_FORWARDED_FOR');
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
        $ip = $request->server('HTTP_X_FORWARDED_FOR');
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $reason = $request->input('comment');
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $checkuser = Permission::checkAdmin($user,$appeal->wiki);
        if ($checkuser) {
            $log = Log::create(array('user' => $user, 'referenceobject'=>$id,'objecttype'=>'appeal','action'=>'comment','reason'=>$reason,'ip' => $ip, 'ua' => $ua . " " .$lang, 'protected'=>0));
            return redirect('appeal/'.$id);
        }
        else {
            $response->assertUnauthorized();
        }
    }
    public function respond($id, $template, Request $request) {
        if (!Auth::check()) {
            abort(403,'No logged in user');
        }
        User::findOrFail(Auth::id())->checkRead();
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->server('HTTP_X_FORWARDED_FOR');
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
        $ip = $request->server('HTTP_X_FORWARDED_FOR');
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
        $ip = $request->server('HTTP_X_FORWARDED_FOR');
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
        $ip = $request->server('HTTP_X_FORWARDED_FOR');
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
        $ip = $request->server('HTTP_X_FORWARDED_FOR');
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
        $ip = $request->server('HTTP_X_FORWARDED_FOR');
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $dev = Permission::checkSecurity($user,"DEVELOPER",$appeal->wiki);
        if ($dev && $appeal->status!=="INVALID") {
            $appeal->status = "INVALID";
            $appeal->save();
            $log = Log::create(array('user' => $user, 'referenceobject'=>$id,'objecttype'=>'appeal','action'=>'invalidate','ip' => $ip, 'ua' => $ua . " " .$lang, 'protected'=>0));
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
        $ip = $request->server('HTTP_X_FORWARDED_FOR');
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $admin = Permission::checkAdmin($user,$appeal->wiki);
        if ($admin) {
            $appeal->status = strtoupper($type);
            $appeal->save();
            $log = Log::create(array('user' => $user, 'referenceobject'=>$id,'objecttype'=>'appeal','action'=>'closed - '.$type,'ip' => $ip, 'ua' => $ua . " " .$lang, 'protected'=>0));
            return redirect('appeal/'.$id);
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
        $ip = $request->server('HTTP_X_FORWARDED_FOR');
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $user = Auth::id();
        $appeal = Appeal::findOrFail($id);
        $admin = Permission::checkAdmin($user,$appeal->wiki);
        if ($admin && $appeal->status!=="PRIVACY") {
            $appeal->status = "PRIVACY";
            $appeal->save();
            $log = Log::create(array('user' => $user, 'referenceobject'=>$id,'objecttype'=>'appeal','action'=>'sent for privacy review','ip' => $ip, 'ua' => $ua . " " .$lang, 'protected'=>0));
            return redirect('appeal/'.$id);
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
        $ip = $request->server('HTTP_X_FORWARDED_FOR');
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
        $ip = $request->server('HTTP_X_FORWARDED_FOR');
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
}
