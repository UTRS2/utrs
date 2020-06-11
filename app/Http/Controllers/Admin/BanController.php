<?php

namespace App\Http\Controllers\Admin;

use App\Ban;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Bans\CreateBanRequest;
use App\Log;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Ban::class);
        $allbans = Ban::all();

        /** @var User $user */
        $user = $request->user();

        $canSeeProtectedBans = false;

        $tableheaders = [ 'ID', 'Target', 'Expires', 'Reason' ];
        $rowcontents = [];

        foreach ($allbans as $ban) {
            $idbutton = '<a href="' . route('admin.bans.view', $ban) . '" class="btn ' . ($ban->is_protected ? 'btn-danger' : 'btn-primary') . '">' . $ban->id . '</a>';
            $targetName = htmlspecialchars($ban->target);

            if ($ban->is_protected) {
                $canSee = $user->can('viewName', $ban);

                if (!$canSeeProtectedBans && $canSee) {
                    $canSeeProtectedBans = true;
                }

                $targetName = $canSee ? '<i class="text-danger">' . $targetName . '</i>'
                    : '<i class="text-muted">(ban target removed)</i>';
            }

            $expiry = Carbon::createFromFormat('Y-m-d H:i:s', $ban->expiry);
            $formattedExpiry = $expiry->year >= 2000 ? $ban->expiry : 'indefinite';

            $rowcontents[$ban->id] = [ $idbutton, $targetName, $formattedExpiry, htmlspecialchars($ban->reason) ];
        }

        $caption = null;
        if ($canSeeProtectedBans) {
            $caption = "Any ban showing in red has been oversighted and should not be shared to others who do not have access to it.";
        }

        return view('admin.tables', [
            'title'        => 'All Bans',
            'tableheaders' => $tableheaders,
            'rowcontents'  => $rowcontents,
            'caption'      => $caption,
            'createlink'   => $user->can('create', Ban::class) ? route('admin.bans.new') : null,
            'createtext'   => 'Create ban',
        ]);
    }

    public function new()
    {
        $this->authorize('create', Ban::class);
        return view('admin.bans.new');
    }

    public function create(CreateBanRequest $request)
    {
        // TODO: check for duplicates

        $ban = DB::transaction(function () use ($request) {
            $ban = Ban::create($request->validated());

            $ip = $request->ip();
            $ua = $request->userAgent();
            $lang = $request->header('Accept-Language');

            Log::create([
                'user' => $request->user()->id,
                'referenceobject' => $ban->id,
                'objecttype' => Ban::class,
                'action' => 'created',
                'reason' => $ban->reason,
                'ip' => $ip,
                'ua' => $ua . ' ' . $lang,
                'protected' => Log::LOG_PROTECTION_NONE,
            ]);

            if ($ban->is_protected) {
                Log::create([
                    'user' => $request->user()->id,
                    'referenceobject' => $ban->id,
                    'objecttype' => Ban::class,
                    'action' => 'oversighted',
                    'reason' => '(oversighted when blocking)',
                    'ip' => $ip,
                    'ua' => $ua . ' ' . $lang,
                    'protected' => Log::LOG_PROTECTION_FUNCTIONARY,
                ]);
            }
        });

        return redirect(route('admin.bans.view'), ['ban' => $ban]);
    }

    public function show(Ban $ban)
    {
        $this->authorize('view', $ban);
        dd($ban);
    }
}
