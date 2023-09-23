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
        if (!$isDeveloper && !$user->hasAnySpecifiedLocalOrGlobalPerms(['global'], ['steward', 'staff'])) {
            $wikis = $wikis
                ->filter(function ($wiki) use ($user) {
                    $neededPermissions = MediaWikiRepository::getWikiPermissionHandler($wiki)
                        ->getRequiredGroupsForAction('appeal_view');
                    return $user->hasAnySpecifiedLocalOrGlobalPerms($wiki, $neededPermissions);
                });
        }

        $appealtypes = [
            'assigned'=>__('appeals.appeal-types.assigned-me'),
            'unassigned'=>__('appeals.appeal-types.unassigned'),
            'reserved'=>__('appeals.appeal-types.reserved'),
        ];
        if($isDeveloper) { $appealtypes['developer']=__('appeals.appeal-types.developer'); }

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
            $query->whereNull('handlingadmin');
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
                //if the appeal is not verified, then add a note to the appealmap
                if ($appeals[$count]->user_verified != 1) {
                    $appealmap[] = ['text'=>'Appeal #'.$appeals[$count]['id'].' is not yet verified and can not be viewed', 'time'=>'INVALID', 'icon'=>'stop','active'=>"error",'appealid'=>$appealid];
                } else {
                    foreach ($appealcomments as $linecomment) {
                        $appealkey = Appeal::findOrFail($appealid)->appealsecretkey;
                        if ($linecomment['action'] == 'create') {
                            $appealmap[] = ['text'=>'Appeal Submitted #'.$appealid, 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'sent','active'=>"yes",'appealid'=>$appealid];
                        }
                        elseif ($linecomment['action'] == 'reserve') {
                            $appealmap[] = ['text'=>'Appeal Assigned to an Administrator', 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'assigned','active'=>"yes",'appealid'=>$appealid];
                        }
                        elseif ($linecomment['action'] == 'verify') {
                            $appealmap[] = ['text'=>'Appeal Verified', 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'verified','active'=>"yes",'appealid'=>$appealid];
                        }
                        elseif ($linecomment['action'] == 'comment') {
                            //we are ignoring internal comments
                        }
                        elseif ($linecomment['action'] == 'responded') {
                            $appealmap[] = ['text'=>'The administrator responded with:', 'time'=>$linecomment['reason'], 'icon'=>'reply','active'=>"yes",'appealid'=>$appealid];
                        }
                        elseif ($linecomment['action'] == 'release') {
                            $appealmap[] = ['text'=>'The appeal has been returned to the queue for a new administrator to review', 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'wait','active'=>"yes",'appealid'=>$appealid];
                        }
                        elseif ($linecomment['action'] == 're-open') {
                            $appealmap[] = ['text'=>'The appeal has been reopened or returned for administrator to review', 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'wait','active'=>"yes",'appealid'=>$appealid];
                        }
                        elseif ($linecomment['action'] == 'transfered appeal to another wiki') {
                            $appealmap[] = ['text'=>'The appeal has been transferred to another wiki for review', 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'transfer','active'=>"yes",'appealid'=>$appealid];
                        }
                        elseif ($linecomment['action'] == 'sent for CheckUser review') {
                            $appealmap[] = ['text'=>'The appeal has been sent to a checkuser for review', 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'queue','active'=>"yes",'appealid'=>$appealid];
                        }
                        elseif ($linecomment['action'] == 'sent for tool administrator review') {
                            $appealmap[] = ['text'=>'The appeal has been sent to a tool administrator for review', 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'queue','active'=>"yes",'appealid'=>$appealid];
                        }
                        elseif ($linecomment['action'] == 'account verified') {
                            $appealmap[] = ['text'=>'The appeal has been verified', 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>'check','active'=>"yes",'appealid'=>$appealid];
                        }
                        //if linecomment action contains "set status as" then based on the remainder of the string, set an appealmap entry
                        elseif (strpos($linecomment['action'], 'set status as') !== false || strpos($linecomment['action'], 'closed - ') !== false || strpos($linecomment['action'], 'closed as') !== false) {
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
                            elseif ($status == 'DECLINE') {
                                $text = 'The administrator declined your appeal';
                                $icon = 'decline';
                                $active = "error";
                                $appealmap[] = ['text'=>$text, 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>$icon,'active'=>$active,'appealid'=>$appealid];
                            }
                            elseif ($status == 'EXPIRE') {
                                $text = 'Your appeal has been closed due to inactivity';
                                $icon = 'time';
                                $active = "error";
                                $appealmap[] = ['text'=>$text, 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>$icon,'active'=>$active,'appealid'=>$appealid];
                            }
                            elseif ($status == 'ACCEPT') {
                                $text = 'Your appeal has been granted';
                                $icon = 'check';
                                $active = "yes";
                                $appealmap[] = ['text'=>$text, 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>$icon,'active'=>$active,'appealid'=>$appealid];
                            }
                            elseif ($status == 'INVALID') {
                                $text = 'Your appeal has been closed without review';
                                $icon = 'decline';
                                $active = "error";
                                $appealmap[] = ['text'=>$text, 'time'=>$linecomment['timestamp'].' - '.$linecomment['user'], 'icon'=>$icon,'active'=>$active,'appealid'=>$appealid];
                            }
                            elseif ($status == 'SKIP') {
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

        elseif ($appeal->user_verified==1)  {
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
