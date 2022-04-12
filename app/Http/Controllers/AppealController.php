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
            $perms['functionary'] = $perms['checkuser'] || $user->hasAnySpecifiedLocalOrGlobalPerms($info->wiki, 'oversight');
            $perms['admin'] = $user->hasAnySpecifiedLocalOrGlobalPerms($info->wiki, 'admin');
            $perms['tooladmin'] = $user->hasAnySpecifiedLocalOrGlobalPerms($info->wiki, 'tooladmin');
            $perms['developer'] = $isDeveloper;

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

        return view('appeals.appeallist', ['appeals' => $appeals, 'appealtypes' => $appealtypes, 'tooladmin' => $isTooladmin, 'noWikis' => $wikis->isEmpty()]);
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

    public function respond(Request $request, Appeal $appeal, Template $template)
    {
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
            'reason' => $template->template,
            'ip' => $ip,
            'ua' => $ua . " " . $lang,
            'protected' => LogEntry::LOG_PROTECTION_NONE,
        ]);

        return redirect()->route('appeal.view', $appeal);
    }

    public function respondCustomSubmit(Request $request, Appeal $appeal)
    {
        $this->authorize('update', $appeal);
        $user = $request->user();

        abort_unless($appeal->handlingadmin === $user->id, 403, 'You are not the handling administrator.');

        $status = $request->validate([
            'status' => ['nullable', new PermittedStatusChange($appeal)],
        ])['status'];

        $ua = $request->userAgent();
        $ip = $request->ip();
        $lang = $request->header('Accept-Language');

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
            'reason' => $request->input('custom'),
            'ip' => $ip,
            'ua' => $ua . " " . $lang,
            'protected' => LogEntry::LOG_PROTECTION_NONE,
        ]);

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
