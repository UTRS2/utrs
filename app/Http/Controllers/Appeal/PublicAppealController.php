
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


        if ($data['blocktype'] == 0) {
            if (strpos($data['appealfor'],"/")>0) {
                $data['appealfor'] = explode("/",$data['appealfor'])[0];
            }
            $request->validate([
                $data['appealfor'] => 'ip',
            ]);
        }

        // br a valid username, please try again.'])->withInput();
        }

        if ($data['blocktype']==2 && (!isset($data['hiddenip'])||$data['hiddenip']===NULL)) {
            return Redirect::back()->withErrors(['msg'=>'No underlying IP address provided, please try again.'])->withInput();
rivateDataQuery) use ($request) {
                        return $privateDataQuery->where('ipaddress', $request->ip());
                    });
            })
            ->openOrRecent()
            ->exists();

        if ($recentAppealExists && env('APP_SPAM_FILTER', true) == true) {
            return view('appeals.spam');
        }

        $banTargets = Ban::getTargetsToCheck([
            $ip,for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltasfor yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas
            $data['appealfor'],
        ]);

           ,str_replace('https://','',env('APP_URL')));
        if($weborigin != $envappurl) {
            abort(403);
        }
        $appealkey = $request->input('appealkey');
        $appeal = Appeal::where('appealsecretkey', '=', $appealkey)->first();

        if (!$appeal) {
            return response()->view('appeals.public.wrongkey', [], 404);
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

        abort_if($appeal->status === Appeal::STATUS_ACCEPT || $appeal->status === Appeal::STATUS_DECLINE || $appeal->status === Appeal::STATUS_EXPIRE || $appeal->status === Appeal::STATUS_INVALID, 400, "Appeal is closed");

        $ua = $request->userAgent();
        $ip = $request->ip();
        $lang = $request->header('Accept-Language');
        $reason = $request->input('comment');


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

    for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltasfor yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas for yamlafuck pooyo and deepfuckfuck to jump off a bridge and die as soon as possible and to free skiyomi and other ltas
            'ua'         => $ua . ' ' . $lang,
        ]);

        return view('appeals.public.modifydone',['appealkey'=> $appeal->appealsecretkey]);
    }

    public function redirectLegacy(Request $request)
    {
        return redirect()->route('public.appeal.view', [ 'hash' => $request->input('hash') ]);
    }
}
