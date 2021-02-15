<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ban;
use App\Models\LogEntry;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Bans\CreateBanRequest;
use App\Http\Requests\Admin\Bans\UpdateBanRequest;
use App\Utils\Logging\RequestLogContext;
use Carbon\Carbon;
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
        $this->authorize('viewAny', Ban::class);
        $allbans = Ban::all();

        /** @var User $user */
        $user = $request->user();

        $protectedBansVisible = false;

        $tableheaders = [ 'ID', 'Target', 'Expires', 'Reason' ];
        $rowcontents = [];

        foreach ($allbans as $ban) {
            $idbutton = '<a href="' . route('admin.bans.view', $ban) . '" class="btn ' . ($ban->is_protected ? 'btn-danger' : 'btn-primary') . '">' . $ban->id . '</a>';
            $targetName = htmlspecialchars($ban->target);

            if ($ban->is_protected) {
                $canSee = $user->can('viewName', $ban);

                if (!$protectedBansVisible && $canSee) {
                    $protectedBansVisible = true;
                }

                $targetName = $canSee ? '<i class="text-danger">' . $targetName . '</i>'
                    : '<i class="text-muted">(ban target removed)</i>';
            }

            $expiry = Carbon::createFromFormat('Y-m-d H:i:s', $ban->expiry);
            $formattedExpiry = $expiry->year >= 2000 ? $ban->expiry : 'indefinite';

            if (!$ban->is_active) {
                $formattedExpiry .= ' <i class="text-muted">(unbanned)</i>';
            }

            if ($expiry->isPast() && $expiry->year >= 2000) {
                $formattedExpiry .= ' <i class="text-danger">(expired)</i>';
            }

            $rowcontents[$ban->id] = [ $idbutton, $targetName, $formattedExpiry, htmlspecialchars($ban->reason) ];
        }

        $caption = null;
        if ($protectedBansVisible) {
            $caption = "Any ban showing in red has been oversighted and should not be shared to others who do not have access to it.";
        }

        return view('admin.tables', [
            'title'        => 'All Bans',
            'tableheaders' => $tableheaders,
            'rowcontents'  => $rowcontents,
            'caption'      => $caption,
            'createlink'   => $user->can('create', Ban::class) ? route('admin.bans.new') : null,
            'createtext'   => 'Add ban',
        ]);
    }

    public function new()
    {
        $this->authorize('create', Ban::class);
        return view('admin.bans.new');
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

        $target = $request->user()->can('viewName', $ban) ? $ban->target : '(ban target removed)';
        $targetHtml = $request->user()->can('viewName', $ban) ? ($ban->is_protected ? '<span class="text-danger">' : '') . htmlspecialchars($ban->target) . ($ban->is_protected ? '</span>' : '')
            : '<i class="text-muted">(ban target removed)</i>';

        $expiry = Carbon::createFromFormat('Y-m-d H:i:s', $ban->expiry);
        $formattedExpiry = $expiry->year >= 2000 ? $ban->expiry : 'indefinite';
        $formOldExpiry = $expiry->year >= 2000 ? $ban->expiry : 'indefinite';

        if (!$ban->is_active) {
            $formattedExpiry .= ' <i class="text-muted">(unbanned)</i>';
        }

        if ($expiry->isPast() && $expiry->year >= 2000) {
            $formattedExpiry .= ' <i class="text-muted">(expired)</i>';
        }

        return view('admin.bans.view', [
            'ban'             => $ban,
            'target'          => $target,
            'targetHtml'      => $targetHtml,
            'formattedExpiry' => $formattedExpiry,
            'formOldExpiry'   => $formOldExpiry,
        ]);
    }

    public function update(UpdateBanRequest $request, Ban $ban)
    {
        DB::transaction(function () use ($request, $ban) {
            $ban->fill($request->validated());

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

            $changeDetails = [];

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
                    $request->input('update_reason', ''),
                    LogEntry::LOG_PROTECTION_FUNCTIONARY
                );
            }
        });

        return redirect()->route('admin.bans.view', [ 'ban' => $ban ]);
    }
}
