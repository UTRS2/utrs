<?php

namespace App\Http\Controllers\Appeal;

use App\Http\Controllers\Controller;
use App\Jobs\GetBlockDetailsJob;
use App\Models\Appeal;
use App\Models\Ban;
use App\Models\LogEntry;
use App\Models\Wiki;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class PublicAppealModifyController extends Controller
{
    public function showForm(Request $request)
    {
        $weborigin = str_replace('http://','',str_replace('https://','',$request->header('origin')));
        $envappurl = str_replace('http://','',str_replace('https://','',env('APP_URL')));
        if($weborigin != $envappurl) {
            abort(403);
        }
        $appealkey = $request->input('appealkey');
        $appeal = Appeal::where('appealsecretkey', $appealkey)->firstOrFail();

        if ($appeal->status !== Appeal::STATUS_NOTFOUND) {
            abort(403, "Appeal is not available to be modified.");
        }

        $wikis = Wiki::where('is_accepting_appeals', true)
            ->get()
            ->mapWithKeys(function (Wiki $wiki) {
                return [$wiki->id => $wiki->display_name];
            });

        return view(
            'appeals.public.modify',
            [
                'appeal' => $appeal,
                'appealkey' => $request->input('appealkey'),
                'wikis' => $wikis,
            ]
        );
    }

    public function submit(Request $request)
    {
        $weborigin = str_replace('http://','',str_replace('https://','',$request->header('origin')));
        $envappurl = str_replace('http://','',str_replace('https://','',env('APP_URL')));
        if($weborigin != $envappurl) {
            abort(403);
        }
        
        $ua = $request->userAgent();
        $ip = $request->ip();
        $lang = $request->header('Accept-Language');
        $appealkey = $request->input('appealkey');

        $appeal = Appeal::where('appealsecretkey', $appealkey)
            ->where('status', Appeal::STATUS_NOTFOUND)
            ->firstOrFail();

        $data = $request->validate([
            'appealfor' => 'required|max:50',
            'wiki_id'   => [
                'required',
                'numeric',
                Rule::exists('wikis', 'id')->where('is_accepting_appeals', true)
            ],
            'blocktype' => 'required|numeric|max:2|min:0',
            'hiddenip'  => 'nullable|ip',
        ]);

        // back compat, at least for now
        $data['wiki'] = Wiki::where('id', $data['wiki_id'])->firstOrFail()->database_name;

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
        
        $recentAppealExists = Appeal::where('id', '!=', $appeal->id)
            ->where(function (Builder $query) use ($request) {
                return $query->where('appealfor', $request->input('appealfor'))
                    ->orWhereHas('privateData', function (Builder $privateDataQuery) use ($request) {
                        return $privateDataQuery->where('ipaddress', $request->ip());
                    });
            })
            ->openOrRecent()
            ->exists();

        if ($recentAppealExists && env('APP_SPAM_FILTER', true) == true) {
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

        return view('appeals.public.modifydone',['appealkey'=> $appealkey]);
    }
}
