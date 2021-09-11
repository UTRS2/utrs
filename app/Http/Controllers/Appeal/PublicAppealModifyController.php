<?php

namespace App\Http\Controllers\Appeal;

use App\Http\Controllers\Controller;
use App\Jobs\GetBlockDetailsJob;
use App\Models\Appeal;
use App\Models\Ban;
use App\Models\LogEntry;
use App\Services\Facades\MediaWikiRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class PublicAppealModifyController extends Controller
{
    public function showForm($hash)
    {
        $appeal = Appeal::where('appealsecretkey', $hash)->firstOrFail();

        if ($appeal->status !== Appeal::STATUS_NOTFOUND) {
            abort(403, "Appeal is not available to be modified.");
        }

        return view('appeals.public.modify', [ 'appeal' => $appeal, 'hash' => $hash ]);
    }

    public function submit(Request $request)
    {
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $hash = $request->input('hash');

        $appeal = Appeal::where('appealsecretkey', $hash)
            ->where('status', Appeal::STATUS_NOTFOUND)
            ->firstOrFail();

        $data = $request->validate([
            'appealfor' => 'required|max:50',
            'wiki'      => [ 'required', Rule::in(MediaWikiRepository::getSupportedTargets()) ],
            'blocktype' => 'required|numeric|max:2|min:0',
            'hiddenip'  => 'nullable|ip',
        ]);

        $banTargets = Ban::getTargetsToCheck([
            $ip,
            $data['appealfor'],
        ]);

        $ban = Ban::whereIn('target', $banTargets)
            ->active()
            ->first();

        if ($ban) {
            return response()
                ->view('appeals.ban', [ 'expire' => $ban->formattedExpiry, 'id' => $ban->id, 'reason' => $ban->reason ])
                ->setStatusCode(403);
        }
        
        $recentAppealExists = Appeal::where('id', '!=', $appeal->id)
            ->where(function (Builder $query) use ($request) {
                return $query->where('appealfor', $request->input('appealfor'))
                    ->orWhereHas('privateData', function (Builder $privateDataQuery) use ($request) {
                        return $privateDataQuery->where('ipaddress', $request->ip());
                    });
            })
            ->openOrRecent()
            ->exists();

        if ($recentAppealExists) {
            return view('appeals.spam');
        }

        $appeal->status = Appeal::STATUS_VERIFY;
        $appeal->update($data);

        LogEntry::create([
            'user_id'         => -1,
            'model_id'        => $appeal->id,
            'model_type'      => Appeal::class,
            'action'          => 'changed block information',
            'ip'              => $ip, 'ua' => $ua . " " . $lang,
        ]);

        GetBlockDetailsJob::dispatch($appeal);

        return redirect()
            ->to(route('public.appeal.view') . '?' . http_build_query([ 'hash' => $appeal->appealsecretkey ]));
    }
}
