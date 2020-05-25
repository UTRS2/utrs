<?php

namespace App\Http\Controllers\Appeal;

use App\Appeal;
use App\Http\Controllers\Controller;
use App\Jobs\GetBlockDetailsJob;
use App\Log;
use Illuminate\Http\Request;

class PublicAppealModifyController extends Controller
{
    public function showForm($hash)
    {
        $appeal = Appeal::where('appealsecretkey', '=', $hash)->firstOrFail();

        if ($appeal->status !== "NOTFOUND") {
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

        $appeal = Appeal::where('appealsecretkey', '=', $hash)
            ->where('status', 'NOTFOUND')
            ->firstOrFail();

        $data = $request->validate([
            'appealfor' => 'required',
            'wiki'      => 'required',
            'blocktype' => 'required|numeric|max:2|min:0',
        ]);

        if ($request['hiddenip'] !== null) {
            $appeal->hiddenip = $request->input('hiddenip');
        }

        $appeal->status = "VERIFY";
        $appeal->update($data);

        Log::create([
            'user'            => -1,
            'referenceobject' => $appeal->id,
            'objecttype'      => 'appeal',
            'action'          => 'correct details',
            'ip'              => $ip, 'ua' => $ua . " " . $lang,
        ]);

        GetBlockDetailsJob::dispatch($appeal);

        return redirect()
            ->to(route('public.appeal.view') . '?' . http_build_query([ 'hash' => $appeal->appealsecretkey ]));
    }
}
