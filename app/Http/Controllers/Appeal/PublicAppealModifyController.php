<?php

namespace App\Http\Controllers\Appeal;

use App\Http\Controllers\Controller;
use App\Jobs\GetBlockDetailsJob;
use App\Models\Appeal;
use App\Models\LogEntry;
use App\MwApi\MwApiUrls;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'wiki'      => [ 'required', Rule::in(MwApiUrls::getSupportedWikis(true)) ],
            'blocktype' => 'required|numeric|max:2|min:0',
            'hiddenip'  => 'nullable|ip',
        ]);

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
