<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Ban;
use App\Log;
use App\Template;
use App\Sitenotice;
use App\Permission;
use App\Wikitask;
use Auth;
use Validator;
use Redirect;
use Illuminate\Support\Arr;

class AdminController extends Controller
{
    public function listusers() {
    	$allusers=User::all();
    	$currentuser = User::findOrFail(Auth::id());
    	$permission=False;
    	$wikilist = explode(",",$currentuser->wikis);
    	foreach($wikilist as $wiki) {
    		if (Permission::checkToolAdmin(Auth::id(),$wiki)==True) {
    			$permission=True;
    		}
    	}
    	if (!$permission) {
    		abort(403);
    	}
    	$tableheaders = ['ID','Username','Verified','Wikis'];
    	$rowcontents = [];
    	foreach ($allusers as $user) {
    		$idbutton = '<a href="/admin/users/'.$user->id.'"><button type="button" class="btn btn-primary">'.$user->id.'</button></a>';
    		if ($user->verified) {$verified = "Yes";}
    		else {$verified="No";}
    		$rowcontents[$user->id] = [$idbutton,$user->username,$verified,$user->wikis];
    	}
    	return view ('admin.tables', ['title'=>'All Users','tableheaders'=>$tableheaders, 'rowcontents'=>$rowcontents]);
    }
    public function listbans() {
    	$allbans=Ban::all();
    	$currentuser = User::findOrFail(Auth::id());
    	$permission=False;
    	$wikilist = explode(",",$currentuser->wikis);
    	foreach($wikilist as $wiki) {
    		if (Permission::checkToolAdmin(Auth::id(),$wiki)==True) {
    			$permission=True;
    		}
    	}
    	if (!$permission) {
    		abort(403);
    	}
    	$tableheaders = ['ID','Target','Expires','Reason'];
    	$rowcontents = [];
    	foreach ($allbans as $ban) {
    		$idbutton = '<a href="/admin/bans/'.$ban->id.'"><button type="button" class="btn btn-primary">'.$ban->id.'</button></a>';
    		$rowcontents[$ban->id] = [$idbutton,$ban->target,$ban->expiry,$ban->reason];
    	}
    	return view ('admin.tables', ['title'=>'All Bans','tableheaders'=>$tableheaders, 'rowcontents'=>$rowcontents]);
    }
    public function listsitenotices() {
    	$allsitenotice=Sitenotice::all();
    	$currentuser = User::findOrFail(Auth::id());
    	$permission=False;
    	$wikilist = explode(",",$currentuser->wikis);
    	foreach($wikilist as $wiki) {
    		if (Permission::checkToolAdmin(Auth::id(),$wiki)==True) {
    			$permission=True;
    		}
    	}
    	if (!$permission) {
    		abort(403);
    	}
    	$tableheaders = ['ID','Message'];
    	$rowcontents = [];
    	foreach ($allsitenotice as $sitenotice) {
    		$idbutton = '<a href="/admin/sitenotices/'.$ban->id.'"><button type="button" class="btn btn-primary">'.$ban->id.'</button></a>';
    		$rowcontents[$ban->id] = [$idbutton,$sitenotice->message];
    	}
    	return view ('admin.tables', ['title'=>'All Sitenotices','tableheaders'=>$tableheaders, 'rowcontents'=>$rowcontents]);
    }
    public function listtemplates() {
    	$alltemplates=Template::all();
    	$currentuser = User::findOrFail(Auth::id());
    	$permission=False;
    	$wikilist = explode(",",$currentuser->wikis);
    	foreach($wikilist as $wiki) {
    		if (Permission::checkToolAdmin(Auth::id(),$wiki)==True) {
    			$permission=True;
    		}
    	}
    	if (!$permission) {
    		abort(403);
    	}
    	$tableheaders = ['ID','Target','Expires','Active'];
    	$rowcontents = [];
    	foreach ($alltemplates as $template) {
    		$idbutton = '<a href="/admin/templates/'.$template->id.'"><button type="button" class="btn btn-primary">'.$template->id.'</button></a>';
    		if($template->active) {$active="Yes";}
    		else {$active="No";}
    		$rowcontents[$template->id] = [$idbutton,$template->name,$template->template,$active];
    	}
    	return view ('admin.tables', ['title'=>'All Templates','tableheaders'=>$tableheaders, 'rowcontents'=>$rowcontents, 'new'=>True]);
    }
    public function verifyAccount() {
    	if (Auth::user()->verified) {
    		return Redirect::to('/home');
    	}
    	else {
    		Wikitask::create(['task'=>'verifyaccount','actionid'=>Auth::id()]);
    		return view('admin.verifyme');
    	}
    }
    public function verify($code) {
    	$user = User::where('u_v_token','=',$code)->first();
    	$user->verified=1;
    	$user->save();
    	return Redirect::to('/home');
    }
    public function makeTemplate(Request $request) {
        dd(Permission::whoami(Auth::id(),"*"));
        if(!Permission::checkToolAdmin(Auth::id(),"*")) {
            abort(401);
        }
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->server('HTTP_X_FORWARDED_FOR');
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $newtemplate = $request->all();
        $name = $newtemplate['name'];
        $template = $newtemplate['template'];
        $creation = Template::create(['name'=>$name,'template'=>$template,'active'=>1]);
        $log = Log::create(array('user' => Auth::id(), 'referenceobject'=>$creation->id,'objecttype'=>'template','action'=>'create','ip' => $ip, 'ua' => $ua . " " .$lang));
        return Redirect::to('/admin/templates');
    }
    public function saveTemplate(Request $request, $id) {
        if(!Permission::checkToolAdmin(Auth::id(),"*")) {
            abort(401);
        }
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->server('HTTP_X_FORWARDED_FOR');
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $data = $request->all();
        $template = Template::findOrFail($id);
        $template->name = $data['name'];
        $template->template = $data['template'];
        $template->save();
        $log = Log::create(array('user' => Auth::id(), 'referenceobject'=>$template->id,'objecttype'=>'template','action'=>'update','ip' => $ip, 'ua' => $ua . " " .$lang));
        return Redirect::to('/admin/templates');
    }
    public function showNewTemplate() {
        return view ('admin.newtemplate');
    }
    public function modifyTemplate($id) {
        $template = Template::findOrFail($id);
        return view ('admin.edittemplate',["template"=>$template]);
    }
}
