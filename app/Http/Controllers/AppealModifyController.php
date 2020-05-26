<?php

namespace App\Http\Controllers;

use App\Appeal;
use App\Log;
use Illuminate\Http\Request;
use Redirect;
use Validator;
use App\Jobs\GetBlockDetailsJob;

class AppealModifyController extends Controller
{
    public function changeip($hash)
    {
        $appeal = Appeal::where('appealsecretkey', '=', $hash)->firstOrFail();
        if ($appeal->status !== "NOTFOUND") {
            abort(403, "Appeal is not availible to be modified.");
        }
        return view('appeals.fixip', ['appeal' => $appeal, 'hash' => $hash]);
    }

    public function changeipsubmit(Request $request, $id)
    {
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $hash = $request->input('hash');
        $appeal = Appeal::where('appealsecretkey', '=', $hash)->firstOrFail();

        $data = $request->validate([
            'appealfor' => 'required',
            'wiki' => 'required',
            'blocktype' => 'required|numeric|max:2|min:0',
            'hiddenip' => 'nullable|ip',
        ]);

        $appeal->status = "VERIFY";
        $appeal->update($data);

        Log::create(array('user' => 0, 'referenceobject' => $appeal->id, 'objecttype' => 'appeal', 'action' => 'modifyip', 'ip' => $ip, 'ua' => $ua . " " . $lang));

        GetBlockDetailsJob::dispatch($appeal);

        return redirect()->to('/publicappeal?hash=' . $appeal->appealsecretkey);
    }
}
