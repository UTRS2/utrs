<?php

namespace App\Http\Controllers\Appeal;

use App\Http\Controllers\Controller;
use App\Http\Rules\SecretEqualsRule;
use App\Jobs\GetBlockDetailsJob;
use App\Models\Appeal;
use App\Models\Ban;
use App\Models\LogEntry;
use App\Models\Privatedata;
use App\Models\Wiki;
use App\Services\Facades\MediaWikiRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PublicAppealController extends Controller
{
    public function store(Request $request)
    {
        $ua = $request->userAgent();
        $ip = $request->ip();
        $lang = $request->header('Accept-Language');

        $data = $request->validate([
            'appealtext' => 'required|max:4000',
            'appealfor'  => 'required|max:50',
            'wiki_id'    => [
                'required',
                'numeric',
                Rule::exists('wikis', 'id')->where('is_accepting_appeals', true)
            ],
            'blocktype'  => 'required|numeric|max:2|min:0',
            'hiddenip'   => 'nullable|ip'
        ]);

        // back compat, at least for now
        $data['wiki'] = Wiki::where('id', $data['wiki_id'])->firstOrFail()->database_name;

        $key = hash('md5', $ip . $ua . $lang . (microtime() . rand()));
        $data['appealsecretkey'] = $key;
        $data['status'] = Appeal::STATUS_VERIFY;
        $data['appealfor'] = trim($data['appealfor']);

        $recentAppealExists = Appeal::where(function (Builder $query) use ($request) {
                return $query
                    ->where('appealfor', $request->input('appealfor'))
                    ->orWhereHas('privateData', function (Builder $privateDataQuery) use ($request) {
                        return $privateDataQuery->where('ipaddress', $request->ip());
                    });
            })
            ->openOrRecent()
            ->exists();

        if ($recentAppealExists) {
            return view('appeals.spam');
        }

        $banTargets = Ban::getTargetsToCheck([
            $ip,
            $data['appealfor'],
        ]);

        $ban = Ban::whereIn('target', $banTargets)
            ->wikiIdOrGlobal($data['wiki_id'])
            ->active()
            ->first();

        if ($ban) {
            return response()
                ->view('appeals.ban', [ 'expire' => $ban->formattedExpiry, 'id' => $ban->id, 'reason' => $ban->reason ])
                ->setStatusCode(403);
        }

        if ($request->has('test_do_not_actually_save_anything')) {
            return response('Test: not actually saving anything');
        }

        /** @var Appeal $appeal */
        $appeal = DB::transaction(function () use ($data, $ip, $ua, $lang) {
            $appeal = Appeal::create($data);

            Privatedata::create([
                'appeal_id' => $appeal->id,
                'ipaddress' => $ip,
                'useragent' => $ua,
                'language'  => $lang,
            ]);

            LogEntry::create([
                'user_id'    => -1,
                'model_id'   => $appeal->id,
                'model_type' => Appeal::class,
                'action'     => 'create',
                'ip'         => $ip,
                'ua'         => $ua . ' ' . $lang,
            ]);

            GetBlockDetailsJob::dispatch($appeal);

            return $appeal;
        });

        return view('appeals.public.makeappeal.hash', [ 'hash' => $appeal->appealsecretkey ]);
    }

    public function view(Request $request)
    {
        $hash = $request->input('hash');
        $appeal = Appeal::where('appealsecretkey', '=', $hash)->first();

        if (!$appeal) {
            return response()->view('appeals.public.wrongkey', [], 404);
        }

        $appeal->loadMissing('comments.userObject');

        return view('appeals.public.appeal', [ 'id' => $appeal->id, 'appeal' => $appeal, ]);
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

        LogEntry::create([
            'user_id'    => -1,
            'model_id'   => $appeal->id,
            'model_type' => Appeal::class,
            'action'     => 'responded',
            'reason'     => $reason,
            'ip'         => $ip,
            'ua'         => $ua . ' ' . $lang,
            'protected'  => LogEntry::LOG_PROTECTION_NONE,
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
        return view('appeals.public.verify', [ 'appeal' => $appeal ]);
    }

    public function verifyAccountOwnership(Request $request, Appeal $appeal)
    {
        abort_unless((strlen($appeal->verify_token) > 0 && strlen($appeal->appealsecretkey) > 0), 400, "This appeal can't be verified");

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

        LogEntry::create([
            'user_id'    => 0,
            'model_id'   => $appeal->id,
            'model_type' => Appeal::class,
            'action'     => 'account verified',
            'ip'         => $ip,
            'ua'         => $ua . ' ' . $lang,
        ]);

        return redirect()
            ->to(route('public.appeal.view') . '?' . http_build_query([ 'hash' => $appeal->appealsecretkey ]));
    }

    public function redirectLegacy(Request $request)
    {
        return redirect()->route('public.appeal.view', [ 'hash' => $request->input('hash') ]);
    }
}
