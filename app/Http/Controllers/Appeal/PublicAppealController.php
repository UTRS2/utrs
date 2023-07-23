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
use App\Utils\IPUtils;
use Redirect;

class PublicAppealController extends Controller
{
    public static function checkValidUser($username, $wiki) {
        
        $api = MediaWikiRepository::getApiForTarget($wiki);
        $services = $api->getAddWikiServices();

        $user = $services->newUserGetter()->getFromUsername($username);
        if($user->getId() > 0) {
            return True;
        } else {
            return False;
        }
    }

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

        if ($data['blocktype'] == 0) {
            if (strpos($data['appealfor'],"/")>0) {
                $data['appealfor'] = explode("/",$data['appealfor'])[0];
            }
            $request->validate([
                $data['appealfor'] => 'ip',
            ]);
        }

        // back compat, at least for now
        $data['wiki'] = Wiki::where('id', $data['wiki_id'])->firstOrFail()->database_name;

        //If blocktype == 0 and appealfor not IP/range
        if ($data['blocktype']==0 && !(IPUtils::isIp($data['appealfor']) || IPUtils::isIpRange($data['appealfor']))) {
            return Redirect::back()->withErrors(['msg'=>'That is not a valid IP address, please try again.'])->withInput();
        }

        if ($data['blocktype']!=0 && (IPUtils::isIp($data['appealfor']) || IPUtils::isIpRange($data['appealfor']))) {
            return Redirect::back()->withErrors(['msg'=>'You need to enter a username, not an IP address, please try again.'])->withInput();
        }
        
        if (($data['blocktype']==2 || $data['blocktype']==1) && !self::checkValidUser($data['appealfor'],$data['wiki'])) {
            return Redirect::back()->withErrors(['msg'=>'You need to enter a valid username, please try again.'])->withInput();
        }

        if ($data['blocktype']==2 && (!isset($data['hiddenip'])||$data['hiddenip']===NULL)) {
            return Redirect::back()->withErrors(['msg'=>'No underlying IP address provided, please try again.'])->withInput();

        }

        if ($data['blocktype']==2 && (!isset($data['hiddenip'])||$data['hiddenip']==NULL)) {
            if (!(IPUtils::isIp($data['hiddenip']) || IPUtils::isIpRange($data['hiddenip']))) {
                return Redirect::back()->withErrors(['msg'=>'The underlying IP is not an IP address, please try again.'])->withInput();
            }
        }

        

        

        $key = hash('sha512', $ip . $ua . $lang . (microtime() . rand()));
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

        if ($recentAppealExists && env('APP_SPAM_FILTER', true) == true) {
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

            GetBlockDetailsJob::dispatchNow($appeal);

            return $appeal;
        });

        return view('appeals.public.makeappeal.hash', [ 'hash' => $appeal->appealsecretkey ]);
    }

    public function view(Request $request)
    {
        $weborigin = str_replace('http://','',str_replace('https://','',$request->header('origin')));
        $envappurl = str_replace('http://','',str_replace('https://','',env('APP_URL')));
        if($weborigin != $envappurl) {
            abort(403);
        }
        $appealkey = $request->input('appealkey');
        $appeal = Appeal::where('appealsecretkey', '=', $appealkey)->first();

        if (!$appeal) {
            return response()->view('appeals.public.wrongkey', [], 404);
        }

        if ($appeal->status == Appeal::STATUS_INVALID) {
            return response()->view('appeals.public.oversight', [], 403);
        }

        $appeal->loadMissing('comments.userObject');

        return view('appeals.public.appeal', [ 'id' => $appeal->id, 'appeal' => $appeal, ]);
    }

    public function addComment(Request $request)
    {
        $weborigin = str_replace('http://','',str_replace('https://','',$request->header('origin')));
        $envappurl = str_replace('http://','',str_replace('https://','',env('APP_URL')));
        if($weborigin != $envappurl) {
            abort(403);
        }
        $appealkey = $request->input('appealsecretkey');
        $appeal = Appeal::where('appealsecretkey', $appealkey)->firstOrFail();

        //get the number of comments made to appeal in the last 24 hours, and make sure it's less than 3 AND get the number of comments made to appeal by user -1 on appeal
        $commentssperday = $appeal->comments()->where('timestamp', '>=', now()->subDays(1))->where('user_id',-1)->where('action','responded')->count();
        $commentsperappeal = $appeal->comments()->where('user_id',-1)->where('action','responded')->count();
        if ($commentssperday >= 3 || $commentsperappeal >= 15) {
            return response()->view('appeals.public.toomanycomments', [], 403);
        }

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

        return view('appeals.public.modifydone',['appealkey'=> $appealkey]);
    }

    public function showVerifyOwnershipForm(Appeal $appeal, string $token)
    {
        //abort_if($appeal->verify_token !== $token, 400, 'Invalid token');
        if ($appeal->verify_token !== $token) {
            return redirect('/')->with('error','Important: Your token to verify is no longer valid. This may be because you have already verified your appeal. Please enter your appeal key below to view the status of the appeal.');
        }
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

        return view('appeals.public.modifydone',['appealkey'=> $appeal->appealsecretkey]);
    }

    public function redirectLegacy(Request $request)
    {
        return redirect()->route('public.appeal.view', [ 'hash' => $request->input('hash') ]);
    }
}
