<?php

namespace App\Http\Controllers\Appeal;

use App\Appeal;
use App\Http\Controllers\Controller;
use App\Jobs\GetBlockDetailsJob;
use App\Log;
use App\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppealActionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Process a appeal status change request
     * @param Request $request Request object to process
     * @param Appeal $appeal Appeal object to process
     * @param string $logEntry Log action name, for example 'reserve' or 'sent for CheckUser review'
     * @param Closure $doAction Closure to take action on the appeal object
     * @param Closure|null $validate Check if this action can be taken. Return a string to fail, or true to pass.
     * @param int $logProtection Log protection level. Use a const from Log::LOG_PROTECTION_*
     * @param string $requiredPermission Permission to take this object
     * @return object Response object
     */
    private function doAction(
        Request $request,
        Appeal $appeal,
        string $logEntry,
        Closure $doAction,
        ?Closure $validate = null,
        int $logProtection = Log::LOG_PROTECTION_NONE,
        string $requiredPermission = 'update'
    )
    {
        // first off, make sure that we can do the action
        $this->authorize($requiredPermission, $appeal);

        if ($validate) {
            $validationResult = $validate($appeal, $request);
            if ($validationResult && $validationResult !== true) {
                abort(403, $validationResult);
                return response($validationResult); // unreachable, in theory
            }
        }

        DB::transaction(function () use ($appeal, $request, $doAction, $logEntry, $logProtection) {
            $ip = $request->ip();
            $ua = $request->userAgent();
            $lang = $request->header('Accept-Language');

            $reason = $doAction($appeal, $request);
            if (!$reason || empty($reason)) {
                $reason = null;
            }

            Log::create([
                'user'            => $request->user()->id,
                'referenceobject' => $appeal->id,
                'objecttype'      => 'appeal',
                'reason'          => $reason,
                'action'          => $logEntry,
                'ip'              => $ip,
                'ua'              => $ua . " " . $lang,
                'protected'       => $logProtection,
            ]);
        });

        return redirect()->route('appeal.view', [ $appeal ]);
    }

    public function reserve(Request $request, Appeal $appeal)
    {
        return $this->doAction(
            $request,
            $appeal,
            'reserve',
            function (Appeal $appeal, Request $request) {
                $appeal->handlingadmin = $request->user()->id;
                $appeal->save();
            },
            function (Appeal $appeal) {
                return $appeal->handlingadmin
                    ? 'This appeal has already been reserved.'
                    : true;
            }
        );
    }

    public function close(Request $request, Appeal $appeal, string $status)
    {
        if (!in_array($status, [ Appeal::STATUS_ACCEPT, Appeal::STATUS_DECLINE, Appeal::STATUS_EXPIRE ])) {
            return abort(400, 'Invalid status.');
        }

        $this->doAction(
            $request,
            $appeal,
            'closed - ' . strtolower($status),
            function (Appeal $appeal) use ($status) {
                $appeal->status = $status;
                $appeal->save();
            }
        );

        return redirect()->route('appeal.list');
    }

    public function release(Request $request, Appeal $appeal)
    {
        return $this->doAction(
            $request,
            $appeal,
            'release',
            function (Appeal $appeal) {
                $appeal->handlingadmin = null;
                $appeal->save();
            },
            function (Appeal $appeal, Request $request) {
                if ($appeal->handlingadmin) {
                    /** @var User $user */
                    $user = $request->user();
                    if ($appeal->handlingadmin === $user->id) {
                        return true;
                    }

                    return $user->hasAnySpecifiedLocalOrGlobalPerms($appeal->wiki, [ 'tooladmin' ])
                        ? true
                        : "Only tool administrators can force release appeals.";
                }

                return 'No-one has reserved this appeal.';
            }
        );
    }

    public function sendToTooladminReview(Request $request, Appeal $appeal)
    {
        return $this->doAction(
            $request,
            $appeal,
            'sent for tool administrator review',
            function (Appeal $appeal) {
                $appeal->status = Appeal::STATUS_ADMIN;
                $appeal->save();
            },
            function (Appeal $appeal) {
                return $appeal->status === Appeal::STATUS_ADMIN
                    ? 'This appeal is already waiting for tool administrator review.'
                    : true;
            }
        );
    }

    public function sendToCheckUserReview(Request $request, Appeal $appeal)
    {
        return $this->doAction(
            $request,
            $appeal,
            'sent for CheckUser review',
            function (Appeal $appeal, Request $request) {
                $reason = $request->validate([
                    'cu_reason' => 'required|string|min:3|max:190',
                ])['cu_reason'];

                $appeal->status = Appeal::STATUS_CHECKUSER;
                $appeal->save();

                return $reason;
            },
            function (Appeal $appeal) {
                return $appeal->status === Appeal::STATUS_CHECKUSER
                    ? 'This appeal is already waiting for CheckUser review.'
                    : true;
            }
        );
    }

    public function reOpen(Request $request, Appeal $appeal)
    {
        return $this->doAction(
            $request,
            $appeal,
            're-open',
            function (Appeal $appeal) {
                $appeal->status = Appeal::STATUS_OPEN;
                $appeal->save();
            },
            function (Appeal $appeal, Request $request) {
                /** @var User $user */
                $user = $request->user();

                if (in_array($appeal->status,
                        [ Appeal::STATUS_ACCEPT, Appeal::STATUS_EXPIRE, Appeal::STATUS_DECLINE,
                          Appeal::STATUS_CHECKUSER, Appeal::STATUS_ADMIN, ])
                    && $user->hasAnySpecifiedLocalOrGlobalPerms($appeal->wiki,
                        [ 'tooladmin', 'checkuser', 'oversight', 'steward', 'staff', ])) {
                    return true;
                }

                return $user->hasAnySpecifiedPermsOnAnyWiki([ 'developer' ])
                    ? true
                    : 'This appeal is currently not in a status where you can re-open it.';
            }
        );
    }

    public function invalidate(Request $request, Appeal $appeal)
    {
        return $this->doAction(
            $request,
            $appeal,
            'closed as invalid',
            function (Appeal $appeal) {
                $appeal->status = Appeal::STATUS_INVALID;
                $appeal->save();
            },
            null,
            Log::LOG_PROTECTION_ADMIN,
            'performDeveloperActions',
        );
    }

    public function reverifyBlockDetails(Request $request, Appeal $appeal)
    {
        return $this->doAction(
            $request,
            $appeal,
            're-verify block details',
            function (Appeal $appeal) {
                GetBlockDetailsJob::dispatch($appeal);
            },
            function (Appeal $appeal) {
                return in_array($appeal->status, [ Appeal::STATUS_VERIFY, Appeal::STATUS_NOTFOUND ])
                    ? true
                    : 'Block details for this appeal have already been found.';
            },
            Log::LOG_PROTECTION_NONE,
            'performDeveloperActions',
        );
    }
}
