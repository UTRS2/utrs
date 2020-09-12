<?php

namespace App\Http\Controllers\Appeal;

use App\Appeal;
use App\Http\Controllers\Controller;
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

                    return $user->hasAnySpecifiedLocalOrGlobalPerms($appeal->wiki, ['tooladmin'])
                        ? true
                        : "Only tool administrators can force release appeals.";
                }

                return 'No-one has reserved this appeal.';
            }
        );
    }

    /**
     * Process a appeal status change request
     * @param Request $request
     * @param Appeal $appeal
     * @param string $logEntry
     * @param Closure $doAction
     * @param Closure|null $validate
     * @param int $logProtection
     * @param string $requiredPermission
     * @return object
     */
    private function doAction(
        Request $request,
        Appeal $appeal,
        string $logEntry,
        Closure $doAction = null,
        $validate = null,
        $logProtection = Log::LOG_PROTECTION_NONE,
        string $requiredPermission = 'update'
    ) {
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
            $doAction($appeal, $request);

            $ip = $request->ip();
            $ua = $request->userAgent();
            $lang = $request->header('Accept-Language');

            Log::create([
                'user'            => $request->user()->id,
                'referenceobject' => $appeal->id,
                'objecttype'      => 'appeal',
                'reason'          => $request->input('reason'),
                'action'          => $logEntry,
                'ip'              => $ip,
                'ua'              => $ua . " " . $lang,
                'protected'       => $logProtection,
            ]);
        });

        return redirect()->route('appeal.view', [$appeal]);
    }
}
