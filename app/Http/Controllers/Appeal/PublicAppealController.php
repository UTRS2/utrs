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
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\IpUtils;

class PublicAppealController extends Controller
{
    public function store(Request $request)
    {
        $ua = $request->userAgent();
        $ip = $request->ip();
        $lang = $request->header('Accept-Language');

        $input = $request->all();
        Arr::forget($input, '_token');
        $input = Arr::add($input, 'status', 'VERIFY');
        $key = hash('md5', $ip . $ua . $lang . (microtime() . rand()));
        $input = Arr::add($input, 'appealsecretkey', $key);

        $request->validate([
            'appealtext' => 'max:4000|required',
            'appealfor'  => 'required',
            'wiki'       => [ 'required', Rule::in(MwApiUrls::getSupportedWikis(true)) ],
            'blocktype'  => 'required|numeric|max:2|min:0',
        ]);

        $recentAppealExists = Appeal::where('appealfor', $request->input('appealfor'))
            ->where(function (Builder $query) {
                return $query
                    ->whereNotIn('status', ['ACCEPT', 'EXPIRE', 'DECLINE'])
                    ->orWhere('submitted', '>=', now()->modify('-2 days'));
            })
            ->exists();

        if ($recentAppealExists) {
            return view('appeals.spam');
        }

        $ban = Ban::where('ip', '=', 0)
            ->where('target', $input['appealfor'])
            ->active()
            ->first();

        if ($ban) {
            return view('appeals.ban', [ 'expire' => $ban->expiry, 'id' => $ban->id ]);
        }

        $banip = Ban::where('ip', '=', 1)
            ->active()
            ->get();

        foreach ($banip as $ban) {
            if (IpUtils::checkIp($ip, $ban->target)) {
                return view('appeals.ban', [ 'expire' => $ban->expiry, 'id' => $ban->id ]);
            }
        }

        $appeal = Appeal::create($input);

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

        abort_if($appeal->status == "ACCEPT" || $appeal->status == "DECLINE" || $appeal->status == "EXPIRE", 400, "Appeal is closed");

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
            'protected'       => 0,
        ]);

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
}
