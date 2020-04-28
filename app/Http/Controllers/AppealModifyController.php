<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Appeal;
use App\Log;
use App\Wikitask;
use Validator;
use Redirect;

class AppealModifyController extends Controller
{
    public function changeip($hash) {
    	$appeal = Appeal::where('appealsecretkey','=',$hash)->firstOrFail();
    	if ($appeal->status !== "NOTFOUND") {
    		abort(403,"Appeal is not availible to be modified.");
    	}
    	return view('appeals.fixip', ['appeal'=>$appeal,'hash'=>$hash]);
    }
    public function changeipsubmit(Request $request,$id) {
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->server('REMOTE_ADDR');
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $input = $request->all();
        $hash = $input['hash'];
        $appeal = Appeal::where('appealsecretkey','=',$hash)->firstOrFail();
        $rules = array(
            'appealfor' => 'required',
            'wiki' => 'required',
            'blocktype' => 'required|numeric|max:2|min:0'
        );
        $validator = Validator::make($input, $rules);

        if ($validator->fails())
        {
            return Redirect::to('/fixappeal/'.$hash)->withInput()->withErrors($validator);
        }
        $appeal->wiki=$request['wiki'];
        $appeal->appealfor=$request['appealfor'];
        $appeal->blocktype=$request['blocktype'];
        $appeal->status="VERIFY";
        $appeal->save();
        $log = Log::create(array('user' => 0, 'referenceobject'=>$appeal['id'],'objecttype'=>'appeal','action'=>'modifyip','ip' => $ip, 'ua' => $ua . " " .$lang));
        Wikitask::create(['task'=>'verifyblock','actionid'=>$appeal->id]);
        return redirect('/');
    }
}
