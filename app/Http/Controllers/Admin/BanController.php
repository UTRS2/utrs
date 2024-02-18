<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ban;
use App\Models\LogEntry;
use App\Models\User;
use App\Models\EmailBan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Bans\CreateBanRequest;
use App\Http\Requests\Admin\Bans\UpdateBanRequest;
use App\Models\Wiki;
use App\Policies\Admin\BanPolicy;
use App\Utils\Logging\RequestLogContext;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $wikis = Wiki::get()
            ->filter(function (Wiki $wiki) use ($user) {
                return $user->can('viewAny', [Ban::class, $wiki]);
            })
            ->pluck('id');

        $canSeeGlobal = $user->can('viewAny', [Ban::class, BanPolicy::WIKI_GLOBAL]);

        if ($wikis->isEmpty() && !$canSeeGlobal) {
            abort(403, "You can't view bans in any wikis!");
            return '';
        }

        $allbans = Ban::paginate(50);

        /** @var User $user */
        $user = $request->user();

        $protectedBansVisible = false;

        $tableheaders = [ 
            __('admin.bans.id'), 
            __('admin.bans.target'), 
            __('admin.bans.expires'), 
            __('admin.bans.reason'),
            "Wiki"
        ];
        
        $admin = $user->hasAnySpecifiedPermsOnAnyWiki(['steward', 'staff', 'developer']);

        //dependent on if the user is a checkuser on any wiki, mark the $checkuser variable
        $checkuser = false;
        //for each wiki the user has permissions on, check if they have checkuser
        foreach ($user->permissions as $permission) {
            if ($permission->hasAnySpecifiedPerms(['checkuser']) || $user->hasAnySpecifiedPermsOnAnyWiki(['steward', 'staff', 'developer'])) {
                $checkuser = true;
            }
        }

        //if the user has tooladmin, steward, staff, or developer globally, then add all wikis to the allowed wikis array
        $allowedwikis = [];
        if ($admin) {
            $allowedwikis = Wiki::get()->pluck('id')->toArray();
        } else {
            //otherwise, add the wikis that the user has permissions on to the allowed wikis array
            foreach ($user->permissions as $permission) {
                if ($permission->hasAnySpecifiedPerms(['tooladmin'])) {
                    $allowedwikis[] = Wiki::where('database_name',$permission->wiki)->first()->id;
                }
            }
        }

        // define createlink as the route to create a new ban
        $createlink = route('admin.bans.new');

        return view('admin.bans', ['title' => __('admin.bans.all'), 'tableheaders' => $tableheaders, 'bans' => $allbans, 'allowed' => $allowedwikis, 'admin' => $admin, 'checkuser' => $checkuser, 'createlink' => $createlink]);
    }

    public function new(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $wikis = $this->constructWikiDropdown($user);

        if (empty($wikis)) {
            abort(403, "You can't ban users in any wikis!");
            return '';
        }

        return view('admin.bans.new', [
            'wikis' => $wikis
        ]);
    }

    public function create(CreateBanRequest $request)
    {
        if (!$request->has('duplicate') && Ban::where($request->only('target', 'ip'))->exists()) {
            throw ValidationException::withMessages([
                'duplicate' => 'It appears that this target has already been blocked. Do you want to continue?',
            ]);
        }

        $ban = DB::transaction(function () use ($request) {
            $ban = Ban::create($request->validated());

            $ban->addLog(
                new RequestLogContext($request),
                'created',
                $request->input('comment', '')
            );

            if ($ban->is_protected) {
                $ban->addLog(
                    new RequestLogContext($request),
                    'oversighted',
                    $request->input('os_reason', ''),
                    LogEntry::LOG_PROTECTION_FUNCTIONARY
                );
            }

            return $ban;
        });

        return redirect()->route('admin.bans.view', [ 'ban' => $ban ]);
    }

    public function show(Request $request, Ban $ban)
    {
        $this->authorize('view', $ban);

        $target = $request->user()->can('viewName', $ban) ? $ban->target : __('admin.bans.ban-target-removed');
        $targetHtml = $request->user()->can('viewName', $ban)
            ? ($ban->is_protected ? '<span class="text-danger">' : '') . htmlspecialchars($ban->target) . ($ban->is_protected ? '</span>' : '')
            : '<i class="text-muted">'.__('admin.bans.ban-target-removed').'</i>';

        $expiry = Carbon::createFromFormat('Y-m-d H:i:s', $ban->expiry);
        $formattedExpiry = $expiry->year >= 2000 ? $ban->expiry : __('admin.bans.indefinite');
        $formOldExpiry = $expiry->year >= 2000 ? $ban->expiry : __('admin.bans.indefinite');

        if (!$ban->is_active) {
            $formattedExpiry .= ' <i class="text-muted">'.__('admin.bans.unbanned').'</i>';
        }

        if ($expiry->isPast() && $expiry->year >= 2000) {
            $formattedExpiry .= ' <i class="text-muted">'.__('admin.bans.expired').'</i>';
        }

        $wikis = $this->constructWikiDropdown($request->user());

        return view('admin.bans.view', [
            'ban'             => $ban,
            'target'          => $target,
            'targetHtml'      => $targetHtml,
            'formattedExpiry' => $formattedExpiry,
            'formOldExpiry'   => $formOldExpiry,
            'wikis'           => $wikis,
        ]);
    }

    public function update(UpdateBanRequest $request, Ban $ban)
    {
        DB::transaction(function () use ($request, $ban) {
            $ban->fill($request->validated());

            $changeDetails = [];

            if ($ban->isDirty('wiki_id')) {
                $newWiki = $ban->wiki_id ? Wiki::find($ban->wiki_id) : null;
                $this->authorize('create', [Ban::class, $newWiki]);
                $changeDetails[] = 'change wiki to ' . ($newWiki ? $newWiki->database_name : 'all UTRS wikis');
            }

            if ($ban->isDirty('is_protected')) {
                $ban->addLog(
                    new RequestLogContext($request),
                    ($ban->is_protected ? '' : 'un-') . 'oversighted',
                    $request->input('os_reason', ''),
                    LogEntry::LOG_PROTECTION_FUNCTIONARY
                );
            }

            $changes = $ban->getDirty();
            $ban->update();

            if (array_key_exists('reason', $changes)) {
                $changeDetails[] = 'reason was set to "' . $ban->reason . '"';
            }

            if (array_key_exists('expiry', $changes)) {
                $expiry = Carbon::createFromFormat('Y-m-d H:i:s', $ban->expiry);
                $formattedExpiry = $expiry->year >= 2000 ? $ban->expiry : 'indefinite';

                $changeDetails[] = 'expiry was set to "' . $formattedExpiry . '"';
            }

            if (array_key_exists('is_active', $changes)) {
                $changeDetails[] = 'ban was ' . ($ban->is_active ? 'enabled' : 'disabled');
            }

            if (!empty($changeDetails)) {
                $ban->addLog(
                    new RequestLogContext($request),
                    'updated - ' . implode(', ', $changeDetails),
                    $request->input('update_reason', '')
                );
            }
        });

        return redirect()->route('admin.bans.view', [ 'ban' => $ban ]);
    }

    private function constructWikiDropdown(User $user): array
    {
        $wikis = Wiki::get()
            ->filter(function (Wiki $wiki) use ($user) {
                return $user->can('create', [Ban::class, $wiki]);
            })
            ->mapWithKeys(function (Wiki $wiki) {
                return [$wiki->id => $wiki->display_name . ' (' . $wiki->database_name . ')'];
            })
            ->toArray();

        if ($user->can('create', [Ban::class, null])) {
            // more hackiness; of course zero or null can't be a collection item key, empty string is valid and nullable (which is important!)
            // also this is an array and not a collection because otherwise this would mess its keys completely :(
            $wikis[''] = 'All UTRS wikis';
        }

        return $wikis;
    }

    public function banEmail(string $code)
    {
        return view('admin.bans.emailban', [
            'code' => $code,
        ]);
    }

    public function banEmailSubmit(Request $request)
    {
        //validate that 
        $this->validate($request, [
            'uid' => 'required|string',
        ]);

        //find the email and set the ban
        $email = EmailBan::where('email', $address)->firstOrFail();
        //set the appealban to match the value from the request
        $email->appealbanned = $request->input('appealbanned', false);
        //set the accountban to match the value from the request
        $email->accountbanned = $request->input('accountbanned', false);


        $ban->addLog(
            new RequestLogContext($request),
            'email ban modified',
            'Appeal: '.$request->input('appealbanned', false) . ' Account: ' . $request->input('accountbanned', false)
        );

        return redirect()->route('admin.bans.view', [ 'ban' => $ban ]);
    }
}
