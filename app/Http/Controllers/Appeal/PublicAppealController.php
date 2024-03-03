<?php

namespace App\Http\Controllers\Appeal;

use App\Http\Controllers\Controller;
use App\Http\Rules\SecretEqualsRule;
use App\Jobs\GetBlockDetailsJob;
use App\Models\Appeal;
use App\Models\Ban;
use App\Models\LogEntry;
use App\Models\Privatedata;
use App\Models\Wiki;
use App\Models\User;
use App\Models\EmailBan;
//use App\Services\Facades\MediaWikiRepository; --Remove - repo is unsupported
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Traits\ProxycheckTrait;
use App\Utils\IPUtils;
use Redirect;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyAccount;
use Illuminate\Support\Str;

class PublicAppealController extends Controller
{
    use ProxycheckTrait;
    public static function checkValidUser($username, $wiki) {
        
        $api = MediaWikiRepository::getApiForTarget($wiki);
        $services = $api->getAddWikiServices();

        $user = $services->newUserGetter()->getFromUsername($username);
        if($user->getId() > 0) {
            return True;
        } else {
            return False;
        }
    }

    public function store(Request $request)
    {
        $ua = $request->userAgent();
        $ip = $request->ip();
        $lang = $request->header('Accept-Language');

        try {
            // store the client hints in a variable
            if($request->header('Sec-CH-UA-Full-Version-List')!== NULL) {
                $clientHints['browser'] = str_replace("\"","",explode(", ",$request->header('Sec-CH-UA-Full-Version-List'))[2]);
                $clientHints['browser'] = str_replace(";v=","/",$clientHints['browser']);
            }
            $clientHints['phone'] = str_replace("\"","",$request->header('Sec-CH-UA-Mobile')== "?1" ? 'true' : 'false');
            $clientHints['platform'] = str_replace("\"","",$request->header('Sec-CH-UA-Platform'));
            $clientHints['platform-version'] = str_replace("\"","",$request->header('Sec-CH-UA-Platform-Version'));
            if (explode(".",$clientHints['platform-version'])[0]>=13 && $clientHints['platform']=='Windows') {
                $clientHints['platform-version'] = '11';
            } elseif($clientHints['platform']=='Windows') {
                $clientHints['platform-version'] = '10 or lower';
            }
            $clientHints['architecture'] = str_replace("\"","",$request->header('Sec-CH-UA-Arch'));
            $clientHints['device-model'] = str_replace("\"","",$request->header('Sec-CH-UA-Model'));
            $clientHints['bits'] = str_replace("\"","",$request->header('Sec-CH-UA-Bitness'));
            $clientHints['resolution'] = $request->header('Sec-CH-Viewport-Width').'x'.$request->header('Sec-CH-Viewport-Height');
            if ($request->header('device-memory')==8) {
                $clientHints['memory'] = '8GB+';
            } else {
                $clientHints['memory'] = $request->header('device-memory')."GB";
            }
            if ($clientHints['platform']=="Windows"||$clientHints['platform']=="Linux") {
                $clientHintsString = $clientHints['platform'] . " " . $clientHints['platform-version'] . " " . $clientHints['architecture'] . "-Arch " . $clientHints['bits'] . "-bit " . $clientHints['resolution'] . " " . $clientHints['memory'].' RAM '. $clientHints['browser'];
            } elseif($clientHints['platform']=="Android") {
                $clientHintsString = $clientHints['platform'] . " " . $clientHints['platform-version'] . " " . $clientHints['device-model'] . " " . $clientHints['bits'] . "-bit " . $clientHints['resolution'] . " " . $clientHints['memory'].' RAM '. $clientHints['browser'];
            } else {
                $clientHintsString = NULL;
            }
            //$clientHints['ua'] = $request->header('Sec-CH-UA');
            if(isset($clientHintsString)) {
                $ua = $ua . ' || CHS: ' . $clientHintsString;
            }
        } catch (\Exception $e) {
            // iterate through the client hints and add them to the user agent string if they exist
            if (isset($clientHints)) {
                $ua = $ua . ' || Raw CHS: ';
                foreach ($clientHints as $key => $value) {
                    if ($value !== false) {
                        $ua = $ua . $key . ': ' . $value . ', ';
                    }
                }
            }
        }

        $data = $request->validate([
            'appealtext' => 'required|max:4000',
            'appealfor'  => 'required|max:50',
            'wiki_id'    => [
                'required',
                'numeric',
                Rule::exists('wikis', 'id')->where('is_accepting_appeals', true)
            ],
            'blocktype'  => 'required|numeric|max:2|min:0',
            'hiddenip'   => 'nullable|ip',
            'email'     => 'nullable|email',
        ]);

        //call the proxycheck laravel trait to check if the IP is a proxy
        $data['proxy'] = $this->proxycheck($ip);

        if ($data['blocktype'] == 0) {
            if (strpos($data['appealfor'],"/")>0) {
                $data['appealfor'] = explode("/",$data['appealfor'])[0];
            }
            $request->validate([
                $data['appealfor'] => 'ip',
            ]);
        }

        // back compat, at least for now
        $data['wiki'] = Wiki::where('id', $data['wiki_id'])->firstOrFail()->database_name;

        //If blocktype == 0 and appealfor not IP/range
        if ($data['blocktype']==0 && !(IPUtils::isIp($data['appealfor']) || IPUtils::isIpRange($data['appealfor']))) {
            return Redirect::back()->withErrors(['msg'=>'That is not a valid IP address, please try again.'])->withInput();
        }

        if ($data['blocktype']!=0 && (IPUtils::isIp($data['appealfor']) || IPUtils::isIpRange($data['appealfor']))) {
            return Redirect::back()->withErrors(['msg'=>'You need to enter a username, not an IP address, please try again.'])->withInput();
        }

        if ($data['blocktype']==2 && (!isset($data['hiddenip'])||$data['hiddenip']===NULL)) {
            return Redirect::back()->withErrors(['msg'=>'No underlying IP address provided, please try again.'])->withInput();

        }

        if ($data['blocktype']==2 && (!isset($data['hiddenip'])||$data['hiddenip']==NULL)) {
            if (!(IPUtils::isIp($data['hiddenip']) || IPUtils::isIpRange($data['hiddenip']))) {
                return Redirect::back()->withErrors(['msg'=>'The underlying IP is not an IP address, please try again.'])->withInput();
            }
        }

        $key = hash('sha512', $ip . $ua . $lang . (microtime() . rand()));
        $data['appealsecretkey'] = $key;
        $data['status'] = Appeal::STATUS_VERIFY;
        $data['appealfor'] = trim($data['appealfor']);
        $data['verify_token'] = Str::random(32);

        $recentAppealExists = Appeal::where(function (Builder $query) use ($request) {
                return $query
                    ->where('appealfor', $request->input('appealfor'))
                    ->orWhereHas('privateData', function (Builder $privateDataQuery) use ($request) {
                        return $privateDataQuery->where('ipaddress', $request->ip());
                    });
            })
            ->openOrRecent()
            ->exists();

        if ($recentAppealExists && env('APP_SPAM_FILTER', true) == true) {
            return view('appeals.spam');
        }

        $banTargets = Ban::getTargetsToCheck([
            $ip,
            $data['appealfor'],
        ]);

        $ban = Ban::whereIn('target', $banTargets)
            ->wikiIdOrGlobal($data['wiki_id'])
            ->active()
            ->first();

        if ($ban) {
            return response()
                ->view('appeals.ban', [ 'expire' => $ban->formattedExpiry, 'id' => $ban->id, 'reason' => $ban->reason ])
                ->setStatusCode(403);
        }

        if ($request->has('test_do_not_actually_save_anything')) {
            return response('Test: not actually saving anything');
        }

        $email = $request->input('email');
        // check if the email domain is in the prohibited domain list in the env file, if so, return with errors to the form page
        if (!is_null($email) && in_array(explode('@', $email)[1], explode(',', env('PROHIBITED_EMAIL_DOMAINS')))) {
            return Redirect::back()->withErrors(['msg'=>'The email domain you used is not allowed to be used for appeals.'])->withInput();
        }

        //check if email is banned and if so, return with errors to the form page
        $emailbans = EmailBan::where('email', '=', $email)->first();
        if (!is_null($emailbans)) {
            if ($emailbans->appealbanned) {
                return Redirect::back()->withErrors(['msg'=>'Your email address has been banned from making appeals. If you believe this is a mistake, please contact a tool administrator.'])->withInput();
            }
            //if the email was used in the last 36 hours, return with errors to the form page
            $lastused = strtotime($emailbans->lastused);
            $now = strtotime(now());
            $diff = $now - $lastused;
            $hours = $diff / ( 60 * 60 );
            if ($hours < 36) {
                return Redirect::back()->withErrors(['msg'=>'Your email address has been used to file an appeal recently. Please make sure your recent appeal has been closed before filing another. If it has, you will need to take time to reflect on the response of the administrator before reappealing.'])->withInput();
            }
        }
        //if the email used in the request has an appeal that is open, return with errors to the form page
        $emailActive = Appeal::where('email', '=', $email)->whereIn('status', Appeal::ACTIVE_APPEAL_STATUSES)->get();
        if (!is_null($email) && count($emailActive)>0) {
            return Redirect::back()->withErrors(['msg'=>'Your email address has an open appeal. Please wait for that appeal to close before submitting another appeal.'])->withInput();
        }

        $emailkey = hash('sha512', $email . (microtime() . rand()));
        //if email exists in the database, update the last used time, otherwise create a new entry
        $emailBanEntry = NULL;
        if (!is_null($emailbans)) {
            $emailbans->lastused = now();
            $linkedappeals = $emailbans->linkedappeals;
            $emailbans->save();
            
        } elseif(!is_null($email)) {
            $emailBanEntry = EmailBan::create([
                'email' => $email,
                'uid' => $emailkey,
                'lastused' => now(),
            ]);
        }
        $wikiemailBanEntry = EmailBan::firstOrCreate([
            'email' => $data['appealfor'].'@wiki',
            'uid' => $emailkey,
            
            'lastused' => now(),
        ]);

        //if appeal is for an IP, send an email to the email address provided using the VerifyAccount mailable
        if ($data['blocktype']==0) {
            $email = $appeal->email;
            if (!is_null($email)) {
                Mail::to($email)->send(new VerifyAccount($email, route('public.appeal.verifyownership', ['appeal' => $appeal->id, 'token' => $appeal->verify_token])));
                $emailBanEntry->lastemail = now();
                $emailBanEntry->save();
            } else {
                //return with errors to the form page
                return Redirect::back()->withErrors(['msg'=>'You must provide an email address to appeal an IP address'])->withInput();
            }
        }

        /** @var Appeal $appeal */
        $appeal = DB::transaction(function () use ($data, $ip, $ua, $lang, $emailbans, $emailBanEntry, $email) {
            $appeal = Appeal::create($data);

            Privatedata::create([
                'appeal_id' => $appeal->id,
                'ipaddress' => $ip,
                'useragent' => $ua,
                'language'  => $lang,
            ]);

            LogEntry::create([
                'user_id'    => -1,
                'model_id'   => $appeal->id,
                'model_type' => Appeal::class,
                'action'     => 'create',
                'ip'         => $ip,
                'ua'         => $ua . ' ' . $lang,
            ]);

            //now that we have the appeal id we need to add it to the emailban entry
            if (!is_null($emailbans)) {
                $linkedappeals = $emailbans->linkedappeals;
                $emailbans->linkedappeals[] = $linkedappeals . ',' . $appeal->id;
                $emailbans->save();
            } elseif(!is_null($email)) {
                $emailBanEntry->linkedappeals = $emailBanEntry->linkedappeals . ',' . $appeal->id;
                $emailBanEntry->save();
            }

            //No longer supported - repo is unsupported
            //Now handled via python script
            //GetBlockDetailsJob::dispatchSync($appeal);

            return $appeal;
        });

        $askproxy = FALSE; 
        if ($data['proxy'] && $data['blocktype']==0) {
            //if the IP is a proxy and the blocktype is IP, this will be given the chance to be diverted to ACC
            //should the user accept, they will be given a form to fill out to request an account
            //if they decline, the appeal will continue as normal           
            return view('appeals.public.makeappeal.divertacc', [ 'hash' => $appeal->appealsecretkey ]);
        }
        elseif($data['proxy'] && $data['blocktype']!=0) {
            $askproxy = TRUE;            
        }
        return view('appeals.public.makeappeal.hash', [ 'hash' => $appeal->appealsecretkey, 'processed' => FALSE, 'askproxy' => $askproxy ]);
    }

    public function submitProxyReason(Request $request) {
        //the user submitted a reason for using a proxy, so we need to update the appeal with the reason
        $appealkey = $request->input('appealkey');
        $appeal = Appeal::where('appealsecretkey', '=', $appealkey)->first();
        $appeal->proxy_reason = $request->input('proxyreason');
        $appeal->save();
        if ($appeal->status == Appeal::STATUS_VERIFY) {
            $processed = FALSE;
        } else {
            $processed = TRUE;
        }
        return view('appeals.public.makeappeal.hash', [ 'hash' => $appeal->appealsecretkey, 'processed' => $processed, 'askproxy' => FALSE ]);
    }

    public function checkStatus(Request $request) {
        //this is an ajax request, so we need to return a json response depending on if the appeal is in verify status or not
        //get the appealkey from the header sent by the ajax request
        $appealkey = $request->header('appealkey');
        //find the appeal with the appealkey
        $appeal = Appeal::where('appealsecretkey', '=', $appealkey)->first();
        //if the appeal doesn't exist, return an error
        if (!$appeal) {
            return response()->json(['status'=>'Failed - appeal not found']);
        }
        
        if ($appeal->status == Appeal::STATUS_VERIFY) {
            return response()->json(['processed'=>FALSE, 'status'=>'success']);
        } else {
            return response()->json(['processed'=>TRUE, 'status'=>'success']);
        }

    }

    public function appealmap(Request $request)
    {
        $weborigin = str_replace('http://','',str_replace('https://','',$request->header('origin')));
        $envappurl = str_replace('http://','',str_replace('https://','',env('APP_URL')));
        if($weborigin != $envappurl) {
            abort(403);
        }
        $appealkey = $request->input('appealkey');
        $appeal = Appeal::where('appealsecretkey', '=', $appealkey)->first();
        $matchAppealID = $appeal->id;

        if (!$appeal) {
            return response()->view('appeals.public.wrongkey', [], 404);
        }

        if ($appeal->status == Appeal::STATUS_INVALID) {
            return response()->view('appeals.public.oversight', [], 403);
        }

        //$appeal->loadMissing('comments.userObject');

        $appeals = Appeal::where('appealfor', '=', $appeal->appealfor)
            ->where('wiki_id', '=', $appeal->wiki_id)
            ->where('status', '!=', Appeal::STATUS_INVALID)
            ->get();

        $allappealcomments = [];

        //for each appeal, get the comments
        foreach ($appeals as $activeappeal) {
            $activeappeal->loadMissing('comments.userObject');
            //sperated by appeal, put the comments in an array
            $allappealcomments[$activeappeal->id] = $activeappeal->comments;
        }

        $fullappealcomments = [];

        //go through $allappealcomments, evalute the comment by action, and put it in $fullappealcomments
        foreach ($allappealcomments as $appealid => $appealcomments) {
            foreach ($appealcomments as $comment) {
                $user = User::find($comment->user_id);
                //if the size of $user is 0, then note that it was the system that made the comment
                if($user == Null) {$user = "SYSTEM";}
                else {$user = $user->username;}

                $fullappealcomments[$appealid][$comment->id] = ['id'=>$appealid,'action'=>$comment->action,'reason'=>$comment->reason,'timestamp'=>$comment->timestamp,'user'=>$user];
            }
        }

        $count = 0;
        $appealmap=[];
        //iterate through $fullappealcomments and each comment to the appeal it belongs to
        foreach ($fullappealcomments as $appealid => $appealcomments) {
            if ($appeals[$count]->user_verified == 1 || $appealid == $matchAppealID) {
                if ($appeals[$count]->user_verified != 1 && $appealid != $matchAppealID) {
                    $appealmap[] = ['text'=>'Appeal #'.$appeals[$count]['id'].' is not yet verified and can not be viewed', 'time'=>'INVALID', 'icon'=>'stop','active'=>"error",'appealid'=>$appealid];
                } else {
                    foreach ($appealcomments as $linecomment) {
                        $appealkey = Appeal::findOrFail($appealid)->appealsecretkey;
                        if ($linecomment['action'] == 'create') {
                            $appealmap[] = ['text'=>'Appeal Submitted #'.$appealid, 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'sent','active'=>"yes",'appealid'=>$appealid];
                        }
                        elseif($linecomment['action'] == 'reserve') {
                            $appealmap[] = ['text'=>'Appeal assigned to an administrator', 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'assigned','active'=>"yes",'appealid'=>$appealid];
                        }
                        elseif($linecomment['action'] == 'verify') {
                            $appealmap[] = ['text'=>'Appeal Verified', 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'verified','active'=>"yes",'appealid'=>$appealid];
                        }
                        elseif($linecomment['action'] == 'comment' || $linecomment['action'] == 'checkuser') {
                            //we are ignoring internal comments
                        }
                        elseif($linecomment['action'] == 'translate') {
                            //we are ignoring internal comments
                        }
                        elseif($linecomment['action'] == 'responded' && $linecomment['user'] != "SYSTEM") {
                            $appealmap[] = ['text'=>__('appeals.map.respond'), 'time'=>$linecomment['reason'], 'icon'=>'reply','active'=>"no",'appealid'=>$appealid];
                        }
                        elseif($linecomment['action'] == 'responded') {
                            $appealmap[] = ['text'=>__('appeals.map.userrespond'), 'time'=>$linecomment['reason'], 'icon'=>'reply','active'=>"yes",'appealid'=>$appealid];
                        }
                        elseif($linecomment['action'] == 'release') {
                            $appealmap[] = ['text'=>'Your appeal has been returned to the queue for a new administrator to review', 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'wait','active'=>"yes",'appealid'=>$appealid];
                        }
                        elseif($linecomment['action'] == 're-open') {
                            $appealmap[] = ['text'=>'Your appeal has been reopened or returned for an administrator to review', 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'wait','active'=>"yes",'appealid'=>$appealid];
                        }
                        elseif($linecomment['action'] == 'transfered appeal to another wiki') {
                            $appealmap[] = ['text'=>'Your appeal has been transferred to another wiki for review', 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'transfer','active'=>"yes",'appealid'=>$appealid];
                        }
                        elseif($linecomment['action'] == 'sent for CheckUser review') {
                            $appealmap[] = ['text'=>'Your appeal has been sent to a checkuser for review', 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'queue','active'=>"yes",'appealid'=>$appealid];
                        }
                        elseif($linecomment['action'] == 'sent for tool administrator review') {
                            $appealmap[] = ['text'=>'Your appeal has been sent to a tool administrator for review', 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'queue','active'=>"yes",'appealid'=>$appealid];
                        }
                        elseif($linecomment['action'] == 'account verified') {
                            $appealmap[] = ['text'=>'You confirmed your identity to appeal #'.$appealid, 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'check','active'=>"yes",'appealid'=>$appealid];
                        }
                        //if linecomment action contains "set status as" then based on the remainder of the string, set an appealmap entry
                        elseif(strpos($linecomment['action'], 'set status as') !== false || strpos($linecomment['action'], 'closed - ') !== false || strpos($linecomment['action'], 'closed as') !== false) {
                            if (strpos($linecomment['action'], 'closed as') !== false) {$status = strtoupper(str_replace('closed as ','',$linecomment['action']));}
                            if (strpos($linecomment['action'], 'set status as') !== false) {$status = str_replace('set status as ','',$linecomment['action']);}
                            if (strpos($linecomment['action'], 'closed - ') !== false) {$status = strtoupper(str_replace('closed - ','',$linecomment['action']));}
                            //run through appeal statuses and make $text human readable
                            if ($status == 'AWAITING_REPLY') {
                                $text = 'The administrator requested a reply from you';
                                $icon = 'paper';
                                $active = "yes";
                                $appealmap[] = ['text'=>$text, 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>$icon,'active'=>$active,'appealid'=>$appealid];
                            }
                            elseif($status == 'DECLINE') {
                                $text = 'The administrator declined your appeal';
                                $icon = 'decline';
                                $active = "error";
                                $appealmap[] = ['text'=>$text, 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>$icon,'active'=>$active,'appealid'=>$appealid];
                            }
                            elseif($status == 'EXPIRE') {
                                $text = 'Your appeal has been closed due to inactivity';
                                $icon = 'time';
                                $active = "error";
                                $appealmap[] = ['text'=>$text, 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>$icon,'active'=>$active,'appealid'=>$appealid];
                            }
                            elseif($status == 'ACCEPT') {
                                $text = 'Your appeal has been granted';
                                $icon = 'check';
                                $active = "yes";
                                $appealmap[] = ['text'=>$text, 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>$icon,'active'=>$active,'appealid'=>$appealid];
                            }
                            elseif($status == 'INVALID') {
                                $text = 'Your appeal has been closed without review';
                                $icon = 'decline';
                                $active = "error";
                                $appealmap[] = ['text'=>$text, 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>$icon,'active'=>$active,'appealid'=>$appealid];
                            }
                            elseif($status == 'SKIP') {
                                //do nothing
                            }
                            else {
                                $text = 'Unhandled status: ' . $status;
                                $icon = 'x';
                                $active = "no";
                                $appealmap[] = ['text'=>$text, 'time'=>$linecomment['timestamp'], 'icon'=>$icon,'active'=>$active,'appealid'=>$appealid];
                            }
                            
                        }
                        else {
                            $appealmap[] = ['text'=>'Not mapped - '.$linecomment['action'] . ' - ' . $linecomment['reason'], 'time'=>'INVALID', 'icon'=>'sent','active'=>"yes",'appealid'=>$appealid];
                        }
                    }
                }
                $count++;
            }
        }
        $route = route('public.appeal.view');
        return view('appeals.public.appealmap', ['appealmap'=>$appealmap,'appealkey'=>$appealkey,'route'=>$route,'appealant'=>$appeal->appealfor,'isdev'=>false,'activeBans'=>FALSE,'matchAppealID'=>$matchAppealID]);
        //return view('appeals.public.appeal', [ 'id' => $appeal->id, 'appeal' => $appeal]);
    }

    public function view(Request $request)
    {
        $weborigin = str_replace('http://','',str_replace('https://','',$request->header('origin')));
        $envappurl = str_replace('http://','',str_replace('https://','',env('APP_URL')));
        if($weborigin != $envappurl) {
            abort(403);
        }
        $appealkey = $request->input('appealkey');
        $appeal = Appeal::where('appealsecretkey', '=', $appealkey)->first();

        if (!$appeal) {
            return response()->view('appeals.public.wrongkey', [], 404);
        }

        if ($appeal->status == Appeal::STATUS_INVALID) {
            return response()->view('appeals.public.oversight', [], 403);
        }

        $appeal->loadMissing('comments.userObject');

        return view('appeals.public.appeal', [ 'id' => $appeal->id, 'appeal' => $appeal, ]);
    }

    public function addComment(Request $request)
    {
        $weborigin = str_replace('http://','',str_replace('https://','',$request->header('origin')));
        $envappurl = str_replace('http://','',str_replace('https://','',env('APP_URL')));
        if($weborigin != $envappurl) {
            abort(403);
        }
        $appealkey = $request->input('appealsecretkey');
        $appeal = Appeal::where('appealsecretkey', $appealkey)->firstOrFail();

        //get the number of comments made to appeal in the last 24 hours, and make sure it's less than 3 AND get the number of comments made to appeal by user -1 on appeal
        $commentssperday = $appeal->comments()->where('timestamp', '>=', now()->subDays(1))->where('user_id',-1)->where('action','responded')->count();
        $commentsperappeal = $appeal->comments()->where('user_id',-1)->where('action','responded')->count();
        if ($commentssperday >= 3 || $commentsperappeal >= 15) {
            return response()->view('appeals.public.toomanycomments', [], 403);
        }

        abort_if($appeal->status === Appeal::STATUS_ACCEPT || $appeal->status === Appeal::STATUS_DECLINE || $appeal->status === Appeal::STATUS_EXPIRE || $appeal->status === Appeal::STATUS_INVALID, 400, "Appeal is closed");

        $ua = $request->userAgent();
        $ip = $request->ip();
        $lang = $request->header('Accept-Language');
        $reason = $request->input('comment');

        LogEntry::create([
            'user_id'    => -1,
            'model_id'   => $appeal->id,
            'model_type' => Appeal::class,
            'action'     => 'responded',
            'reason'     => $reason,
            'ip'         => $ip,
            'ua'         => $ua . ' ' . $lang,
            'protected'  => LogEntry::LOG_PROTECTION_NONE,
        ]);

        if ($appeal->status === Appeal::STATUS_AWAITING_REPLY) {
            $appeal->update([
                'status' => Appeal::STATUS_OPEN,
            ]);
        }

        return view('appeals.public.modifydone',['appealkey'=> $appealkey]);
    }

    public function showVerifyOwnershipForm(Appeal $appeal, string $token)
    {
        //abort_if($appeal->verify_token !== $token, 400, 'Invalid token');
        if ($appeal->verify_token !== $token) {
            return redirect('/')->with('error','Important: Your token to verify is no longer valid. This may be because you have already verified your appeal. Please enter your appeal key below to view the status of the appeal.');
        }
        return view('appeals.public.verify', [ 'appeal' => $appeal ]);
    }

    public function verifyAccountOwnership(Request $request, Appeal $appeal)
    {
        abort_unless((strlen($appeal->verify_token) > 0 && strlen($appeal->appealsecretkey) > 0), 400, "This appeal can't be verified");

        $request->validate([
            'verify_token' => [ 'required', new SecretEqualsRule($appeal->verify_token) ],
            'secret_key'   => [ 'required', new SecretEqualsRule($appeal->appealsecretkey) ],
        ]);

        $appeal->update([
            'verify_token'  => null,
            'user_verified' => true,
        ]);

        $ua = $request->userAgent();
        $ip = $request->ip();
        $lang = $request->header('Accept-Language');

        LogEntry::create([
            'user_id'    => 0,
            'model_id'   => $appeal->id,
            'model_type' => Appeal::class,
            'action'     => 'account verified',
            'ip'         => $ip,
            'ua'         => $ua . ' ' . $lang,
        ]);

        return view('appeals.public.modifydone',['appealkey'=> $appeal->appealsecretkey]);
    }

    public function redirectLegacy(Request $request)
    {
        return redirect()->route('public.appeal.view', [ 'hash' => $request->input('hash') ]);
    }
}
