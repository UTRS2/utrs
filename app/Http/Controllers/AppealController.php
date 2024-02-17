<?php

namespace App\Http\Controllers;

use App\Http\Rules\PermittedStatusChange;
use App\Models\Appeal;
use App\Models\LogEntry;
use App\Models\Wiki;
use App\Models\Old\Oldappeal;
use App\Models\Old\Olduser;
use App\Models\Privatedata;
use App\Models\Template;
use App\Models\User;
use App\Models\Ban;
use App\Models\Translation;
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

            $translateIDs = [];
            // check if the appealtext has a translation in the users default language under log id 0
            $translation = Translation::where('language', $user->default_translation_language)->where('log_entries_id', 0)->first();
            if ($translation) {
                $info->appealtext = $translation->translation;
                $translateIDs[] = 0;
            }

            $logs = $info->comments;
            $i=0;
            // review each comment and check if there is a translation for it, and if so, load it instead based on the users default language
            foreach ($logs as $log) {
                $translation = $log->translations()->where('language', $user->default_translation_language)->first();
                if (isset($translation->translation)) {
                    if ($translation->translation != null) {
                        $logs[$i]->reason = $translation->translation;
                        $translateIDs[] = $log->id;
                    }
                }
                $i=$i+1;
            }

            $cudata = Privatedata::where('appeal_id', '=', $id)->get()->first();

            $perms = [];
            $perms['checkuser'] = $user->can('viewCheckUserInformation', $info);
            $perms['oversight'] = $user->hasAnySpecifiedLocalOrGlobalPerms($info->wiki, 'oversight');
            $perms['functionary'] = $perms['checkuser'] || $perms['oversight'];
            $perms['admin'] = $user->hasAnySpecifiedLocalOrGlobalPerms($info->wiki, 'admin');
            $perms['tooladmin'] = $user->hasAnySpecifiedLocalOrGlobalPerms($info->wiki, 'tooladmin');
            $perms['developer'] = $isDeveloper;
            
            $perms['steward'] = $user->hasAnySpecifiedLocalOrGlobalPerms($info->wiki, 'steward');

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

            $urlname = str_replace("+","_",urlencode($info->appealfor));

            $wikis = \array_diff(collect(MediaWikiRepository::getSupportedTargets())->toArray(),[$info->wiki]);
            $newwikis=[];

            foreach ($wikis as $wiki) {
                $newwikis[] = [MediaWikiRepository::getID($wiki)=>$wiki];
            }

            return view('appeals.appeal', [
                'id' => $id,
                'info' => $info,
                'comments' => $logs,
                'cudata' => $cudata,
                'checkuserdone' => $checkuserdone,
                'perms' => $perms,
                'previousAppeals' => $previousAppeals,
                'urlname' => $urlname,
                'wikis' => $newwikis,
                'translateIDs' => $translateIDs,
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

    public function appeallist(Appeal $appeal)
    {
        abort_unless(Auth::check(), 403, 'No logged in user');
        /** @var User $user */
        $user = Auth::user();

        if ($user->email == NULL) {
            $noemail = true;
        }
        else {
            $noemail = false;
        }

        $isDeveloper = $user->hasAnySpecifiedPermsOnAnyWiki('developer');
        $isTooladmin = $isDeveloper || $user->hasAnySpecifiedPermsOnAnyWiki('tooladmin');
        $isCUAnyWiki = $isDeveloper || $user->hasAnySpecifiedPermsOnAnyWiki('checkuser');
        $isStewClerk = $user->hasAnySpecifiedPermsOnAnyWiki('stew_clerk');

        $wikis = collect(MediaWikiRepository::getSupportedTargets());

        // For users who aren't developers, stewards or staff, show appeals only for own wikis
        if (!$isDeveloper && !$user->hasAnySpecifiedLocalOrGlobalPerms(['global'], ['steward', 'staff'])) {
            $wikis = $wikis
                ->filter(function ($wiki) use ($user) {
                    if($user->hasAnySpecifiedLocalOrGlobalPerms($wiki, ['stew_clerk'])) {
                        return true;
                    }
                    $neededPermissions = MediaWikiRepository::getWikiPermissionHandler($wiki)
                        ->getRequiredGroupsForAction('appeal_view');
                    return $user->hasAnySpecifiedLocalOrGlobalPerms($wiki, $neededPermissions);
                });
        }

        $appealtypes = [
            'all'=>'Active appeals',
        ];
        if($isDeveloper) { $appealtypes['developer']=__('appeals.appeal-types.developer'); }

        $developerStatuses = [Appeal::STATUS_VERIFY, Appeal::STATUS_NOTFOUND];
        $basicStatuses = [Appeal::STATUS_ACCEPT, Appeal::STATUS_DECLINE, Appeal::STATUS_EXPIRE, Appeal::STATUS_VERIFY, Appeal::STATUS_NOTFOUND, Appeal::STATUS_INVALID, Appeal::STATUS_CHECKUSER];

        $appeals[$appealtypes['all']] = $appeal->whereIn('wiki', $wikis)->where(function ($query) use ($basicStatuses) {
            $query->whereNotIn('status', $basicStatuses);
        })
        ->sortable();
        $mainPaginate = $appeals[$appealtypes['all']]->count() > 49;
        if($appeals[$appealtypes['all']]->count() > 49) {
            $appeals[$appealtypes['all']] = $appeals[$appealtypes['all']]->paginate(25);
        }
        else {
            $appeals[$appealtypes['all']] = $appeals[$appealtypes['all']]->get();
        }

        if($isDeveloper) {
            $appeals[$appealtypes['developer']] = $appeal->whereIn('status',$developerStatuses)
            ->sortable()->paginate(20);
        }
        
        return view('appeals.appeallist', ['appeals' => $appeals, 'appealtypes' => $appealtypes, 'tooladmin' => $isTooladmin, 'noWikis' => $wikis->isEmpty(), 'mainPaginate' => $mainPaginate, 'noemail' => $noemail]);
    }

    public function map(Request $request,$id) {
        if (!Auth::check()) {
            if ($request->has('send_to_oauth')) {
                // fancy tricks to set intended path as cookie, but without the GET param
                $redirect = redirect();
                $redirect->setIntendedUrl(app(UrlGenerator::class)->previous());
                return $redirect->route('login');
            }

            return response()->view('appeals.public.needauth', [], 401);
        }
        else {
            $appeal = Appeal::find($id);
            $this->authorize('view', $appeal);
            if (!$appeal) {
                return abort('404');
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
            $allappealnumbers= [];

            $activeBans= Ban::where('is_active',1)->where('target',$appeal->appealfor)->where('is_protected',0)->where('wiki_id',$appeal->wiki_id)->active()->first();

            //for each appeal, get the comments
            foreach ($appeals as $activeappeal) {
                $activeappeal->loadMissing('comments.userObject');
                //sperated by appeal, put the comments in an array
                $allappealcomments[$activeappeal->id]['status'] = $activeappeal->status;
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
                //if the appeal is not verified, then add a note to the map
                //also if the appeal is the same as the one we are viewing, then move to the else
                if ($appeals[$count]->user_verified != 1 && $appealid != $id) {
                    $appealmap[] = ['text'=>'Appeal #'.$appeals[$count]['id'].' is not yet verified and can not be viewed', 'time'=>'INVALID', 'icon'=>'stop','active'=>"error",'appealid'=>$appealid];
                } else {
                    foreach ($appealcomments as $linecomment) {
                        $appealkey = Appeal::findOrFail($appealid)->appealsecretkey;
                        if ($linecomment['action'] == 'create') {
                            $appealmap[] = ['text'=>__('appeals.map.submitted',['id' =>$appealid]), 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'sent','active'=>"yes",'appealid'=>$appealid];
                        }
                        elseif($linecomment['action'] == 'reserve') {
                            $appealmap[] = ['text'=>__('appeals.map.assigned'), 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'assigned','active'=>"yes",'appealid'=>$appealid];
                        }
                        elseif($linecomment['action'] == 'verify') {
                            $appealmap[] = ['text'=>__('appeals.map.verified'), 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'verified','active'=>"yes",'appealid'=>$appealid];
                        }
                        elseif($linecomment['action'] == 'comment') {
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
                            $appealmap[] = ['text'=>__('appeals.map.released'), 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'wait','active'=>"yes",'appealid'=>$appealid];
                        }
                        elseif($linecomment['action'] == 're-open') {
                            $appealmap[] = ['text'=>__('appeals.map.reopen'), 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'wait','active'=>"yes",'appealid'=>$appealid];
                        }
                        elseif($linecomment['action'] == 'transfered appeal to another wiki') {
                            $appealmap[] = ['text'=>__('appeals.map.transfer'), 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'transfer','active'=>"yes",'appealid'=>$appealid];
                        }
                        elseif($linecomment['action'] == 'sent for CheckUser review') {
                            $appealmap[] = ['text'=>__('appeals.map.checkuser'), 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'queue','active'=>"yes",'appealid'=>$appealid];
                        }
                        elseif($linecomment['action'] == 'sent for tool administrator review') {
                            $appealmap[] = ['text'=>__('appeals.map.admin'), 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'queue','active'=>"yes",'appealid'=>$appealid];
                        }
                        elseif($linecomment['action'] == 'account verified') {
                            $appealmap[] = ['text'=>__('appeals.map.verifiedaccount'), 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'check','active'=>"yes",'appealid'=>$appealid];
                        }
                        //if linecomment action contains "set status as" then based on the remainder of the string, set an map entry
                        elseif(strpos($linecomment['action'], 'set status as') !== false || strpos($linecomment['action'], 'closed - ') !== false || strpos($linecomment['action'], 'closed as') !== false) {
                            if (strpos($linecomment['action'], 'closed as') !== false) {$status = strtoupper(str_replace('closed as ','',$linecomment['action']));}
                            if (strpos($linecomment['action'], 'set status as') !== false) {$status = str_replace('set status as ','',$linecomment['action']);}
                            if (strpos($linecomment['action'], 'closed - ') !== false) {$status = strtoupper(str_replace('closed - ','',$linecomment['action']));}
                            //run through appeal statuses and make $text human readable
                            if ($status == 'AWAITING_REPLY') {
                                $text = __('appeals.map.awaitreply');
                                $icon = 'paper';
                                $active = "yes";
                                $appealmap[] = ['text'=>$text, 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>$icon,'active'=>$active,'appealid'=>$appealid];
                            }
                            elseif($status == 'DECLINE') {
                                $text = __('appeals.map.declined');
                                $icon = 'decline';
                                $active = "error";
                                $appealmap[] = ['text'=>$text, 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>$icon,'active'=>$active,'appealid'=>$appealid];
                            }
                            elseif($status == 'EXPIRE') {
                                $text = __('appeals.map.expired');
                                $icon = 'time';
                                $active = "error";
                                $appealmap[] = ['text'=>$text, 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>$icon,'active'=>$active,'appealid'=>$appealid];
                            }
                            elseif($status == 'ACCEPT') {
                                $text = __('appeals.map.accepted');
                                $icon = 'check';
                                $active = "yes";
                                $appealmap[] = ['text'=>$text, 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>$icon,'active'=>$active,'appealid'=>$appealid];
                            }
                            elseif($status == 'INVALID') {
                                $text = __('appeals.map.invalid');
                                $icon = 'decline';
                                $active = "error";
                                $appealmap[] = ['text'=>$text, 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>$icon,'active'=>$active,'appealid'=>$appealid];
                            }
                            elseif($status == 'SKIP') {
                                //do nothing
                            }
                            else {
                                $text = __('appeals.map.unhandled', $status);
                                $icon = 'x';
                                $active = "no";
                                $appealmap[] = ['text'=>$text, 'time'=>$linecomment['timestamp'], 'icon'=>$icon,'active'=>$active,'appealid'=>$appealid];
                            }
                            
                        }
                        else {
                            $appealmap[] = ['text'=>'Not mapped - '.$linecomment['action'] . ' - ' . $linecomment['reason'], 'time'=>'INVALID', 'icon'=>'sent','active'=>"yes",'appealid'=>$appealid,'matchAppealID'=>0];
                        }
                    }
                }
                $count++;
            }

            $route = '/appeal/'.$request->input('id');
            $appealkey = "";
            $user = Auth::user();
            $isDeveloper = $user->hasAnySpecifiedPermsOnAnyWiki('developer');
            return view('appeals.public.appealmap', ['appealmap'=>$appealmap,'appealkey'=>$appealkey,'route'=>$route,'appealant'=>$appeal->appealfor,'isdev'=>$isDeveloper,'activeBans'=>$activeBans]);
        }
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

    public function respond(Request $request, Appeal $appeal, Template $template=NULL)
    {
        if(!$template) {
            $respondText = $request->input('custom');
        }
        else {
            $respondText = $template->template;
        }
        $this->authorize('update', $appeal);
        $user = $request->user();

        abort_unless($appeal->handlingadmin === $user->id, 403, 'You are not the handling administrator.');

        $ua = $request->userAgent();
        $ip = $request->ip();
        $lang = $request->header('Accept-Language');

        $status = $request->validate([
            'status' => ['nullable', new PermittedStatusChange($appeal)],
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
            'reason' => $respondText,
            'ip' => $ip,
            'ua' => $ua . " " . $lang,
            'protected' => LogEntry::LOG_PROTECTION_NONE,
        ]);
        
        if ($appeal->user_verified==1 && !in_array($appeal->status, Appeal::APPEAL_CLOSED)) {
            $title = 'UTRS appeal response';
            $baseURL = route('home');
            $message = <<<EOF
                Hello,
                Your appeal, #$appeal->id, has be reviewed and the following message was left for you:

                $respondText

                Please reply by going to the following link and entering your appealkey: $baseURL
                In case you forgot your appealkey, it is: $appeal->appealsecretkey

                Thanks,
                $user->username
                EOF;
            $result = MediaWikiRepository::getApiForTarget($appeal->wiki)->getMediaWikiExtras()->sendEmail($appeal->getWikiEmailUsername(), $title, $message);
        }

        elseif($appeal->user_verified==1)  {
            $title = 'UTRS appeal response';
            $baseURL = route('home');
            switch ($appeal->status) {
                 case Appeal::STATUS_ACCEPT:
                     $textStatus = "has been accepted";
                     break;
                case Appeal::STATUS_DECLINE:
                     $textStatus = "has been declined";
                     break;
                case Appeal::STATUS_EXPIRE:
                     $textStatus = "has expired";
                     break;
                 default:
                     $textStatus = "has been reviewed";
                     break;
            }

            $message = <<<EOF
                Hello,
                Your appeal, #$appeal->id, $textStatus and the following message was left for you:

                $respondText

                Your appeal is now closed. You will need to take time to consider the reply from the administrator. Should you wish to file a new appeal, you will need to wait a few days to do so, to ensure that you have thought about the administrator's reply. 
                You can still view it by going to the following link and entering your appealkey: $baseURL
                In case you forgot your appealkey, it is: $appeal->appealsecretkey

                Thanks,
                $user->username
                EOF;
            $result = MediaWikiRepository::getApiForTarget($appeal->wiki)->getMediaWikiExtras()->sendEmail($appeal->getWikiEmailUsername(), $title, $message);
        }

        return redirect()->route('appeal.view', $appeal);
    }

    public function viewtemplates(Request $request, Appeal $appeal)
    {
        $this->authorize('update', $appeal);

        $templates = Template::where('active', true)
            ->where('wiki_id', $appeal->wiki_id)
            ->get();

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
