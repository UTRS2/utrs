<?php

namespace App\Http\Controllers\Appeal;

use App\Appeal;
use App\Ban;
use App\Http\Controllers\Controller;
use App\Jobs\GetBlockDetailsJob;
use App\Log;
use App\MwApi\MwApiUrls;
use App\Privatedata;
use App\Rules\SecretEqualsRule;
use App\Sendresponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log as LaravelLog;
use Symfony\Component\HttpFoundation\IpUtils;

class PublicAppealController extends Controller
{
    public function store(Request $request)
    {
        $ua = $request->userAgent();
        $ip = $request->ip();
        $lang = $request->header('Accept-Language');

        $data = $request->validate([
            'appealtext' => 'required|max:4000',
            'appealfor'  => 'required',
            'wiki'       => [ 'required', Rule::in(MwApiUrls::getSupportedWikis(true)) ],
            'blocktype'  => 'required|numeric|max:2|min:0',
        ]);

        $key = hash('md5', $ip . $ua . $lang . (microtime() . rand()));
        $data['appealsecretkey'] = $key;
        $data['status'] = Appeal::STATUS_VERIFY;

        $recentAppealExists = Appeal::where('appealfor', $request->input('appealfor'))
            ->where(function (Builder $query) {
                return $query
                    ->whereNotIn('status', [Appeal::STATUS_ACCEPT, Appeal::STATUS_DECLINE, Appeal::STATUS_EXPIRE])
                    ->orWhere('submitted', '>=', now()->modify('-2 days'));
            })
            ->exists();

        if ($recentAppealExists) {
            return view('appeals.spam');
        }

        $ban = Ban::where('ip', '=', 0)
            ->where('target', $data['appealfor'])
            ->active()
            ->first();

        if ($ban) {
            return view('appeals.ban', [ 'expire' => $ban->expiry, 'id' => $ban->id ]);
        }

        // TODO: do not loop thru all existing ip bans, instead search for specific bans for the IP (range)
        $banip = Ban::where('ip', '=', 1)
            ->active()
            ->get();

        foreach ($banip as $ban) {
            if (IpUtils::checkIp($ip, $ban->target)) {
                return view('appeals.ban', [ 'expire' => $ban->expiry, 'id' => $ban->id ]);
            }
        }

        $appeal = DB::transaction(function () use ($data, $ip, $ua, $lang) {
            $appeal = Appeal::create($data);

            Privatedata::create([
                'appealID'  => $appeal->id,
                'ipaddress' => $ip,
                'useragent' => $ua,
                'language'  => $lang,
            ]);

            Log::create([
                'user'            => -1,
                'referenceobject' => $appeal->id,
                'objecttype'      => 'appeal',
                'action'          => 'create',
                'ip'              => $ip,
                'ua'              => $ua . ' ' . $lang,
            ]);

            GetBlockDetailsJob::dispatch($appeal);
        });

        /**
         * Yes, this is a hard hack and not optimal, but we are still
         * allowing these appeals to be created till other master tasks
         * either prevent it or we go live with those wikis
         **/
        if ($appeal->wiki == "ptwiki" || $appeal->wiki == "global") {
            LaravelLog::warning('An appeal has been created on an unsupported wiki. AppealID #'.$appeal->id);
        }

        return view('appeals.public.makeappeal.hash', [ 'hash' => $key ]);
    }

    public function view(Request $request)
    {
        $hash = $request->input('hash');
        $appeal = Appeal::where('appealsecretkey', '=', $hash)->firstOrFail();

        $appeal->loadMissing('comments.userObject');

        $replies = Sendresponse::where('appealID', '=', $appeal->id)->where('custom', '!=', 'null')->get();
        return view('appeals.public.appeal', [ 'id' => $appeal->id, 'appeal' => $appeal, 'replies' => $replies ]);
    }

    public function addComment(Request $request)
    {
        $key = $request->input('appealsecretkey');
        $appeal = Appeal::where('appealsecretkey', $key)->firstOrFail();

        abort_if($appeal->status === Appeal::STATUS_ACCEPT || $appeal->status === Appeal::STATUS_DECLINE || $appeal->status === Appeal::STATUS_EXPIRE || $appeal->status === Appeal::STATUS_INVALID, 400, "Appeal is closed");

        $ua = $request->userAgent();
        $ip = $request->ip();
        $lang = $request->header('Accept-Language');
        $reason = $request->input('comment');

        Log::create([
            'user'            => -1,
            'referenceobject' => $appeal->id,
            'objecttype'      => 'appeal',
            'action'          => 'responded',
            'reason'          => $reason,
            'ip'              => $ip,
            'ua'              => $ua . ' ' . $lang,
            'protected'       => Log::LOG_PROTECTION_NONE,
        ]);

        if ($appeal->status === Appeal::STATUS_AWAITING_REPLY) {
            $appeal->update([
                'status' => Appeal::STATUS_OPEN,
            ]);
        }

        return redirect()->back();
    }

    public function showVerifyOwnershipForm(Appeal $appeal, string $token)
    {
        abort_if($appeal->verify_token !== $token, 400, 'Invalid token');
        return view('appeals.verifyaccount', [ 'appeal' => $appeal ]);
    }

    public function verifyAccountOwnership(Request $request, Appeal $appeal)
    {
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

        Log::create([
            'user'            => 0,
            'referenceobject' => $appeal->id,
            'objecttype'      => 'appeal',
            'action'          => 'account verifed',
            'ip'              => $ip,
            'ua'              => $ua . ' ' . $lang,
        ]);

        return redirect()
            ->to(route('public.appeal.view') . '?' . http_build_query([ 'hash' => $appeal->appealsecretkey ]));
    }

    public function redirectLegacy(Request $request)
    {
        return redirect()->route('public.appeal.view', ['hash' => $request->input('hash')]);
    }
}
