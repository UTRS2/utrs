<?php

namespace App\Http\Controllers\Appeal;

use App\Appeal;
use App\Http\Controllers\Controller;
use App\Jobs\GetBlockDetailsJob;
use App\Log;
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

        Log::create([
            'user'            => -1,
            'referenceobject' => $appeal->id,
            'objecttype'      => 'appeal',
            'action'          => 'changed block information',
            'ip'              => $ip, 'ua' => $ua . " " . $lang,
        ]);

        GetBlockDetailsJob::dispatch($appeal);

        return redirect()
            ->to(route('public.appeal.view') . '?' . http_build_query([ 'hash' => $appeal->appealsecretkey ]));
    }
}
