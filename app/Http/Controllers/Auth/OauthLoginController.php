<?php

namespace App\Http\Controllers\Auth;

use Log;
use App\Models\LogEntry;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class OauthLoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->only(['login', 'callback']);
        $this->middleware('auth')->only('logout');
    }

    public function login()
    {
        return Socialite::driver('mediawiki')
            ->redirect();
    }

    public function callback(Request $request)
    {
        // run this inside a transaction.
        // helps mainly for development stuff, when loading permissions fails
        $user = DB::transaction(function () use ($request) {

            try {
                $socialiteUser = Socialite::driver('mediawiki')->user();
            } catch (Exception $e) {
                //the previous sanity check code didn't account for OAuth tokens being undefinited, and therefore causing an error
                //We are not concerned about logging this as it's a user issue, easily fixed.
                abort(400); 
            }


            $user = User::firstWhere([
                'username' => $socialiteUser->getName(),
            ]);

            if (!$user) {
                $user = User::firstOrCreate([
                    'mediawiki_id' => $socialiteUser->getId(),
                ], [
                    'username' => $socialiteUser->getName(),
                ]);
            }

            if ($user->mediawiki_id !== $socialiteUser->getId()) {
                $user->mediawiki_id = $socialiteUser->getId();
                $user->save();
            }

            if ($user->username !== $socialiteUser->getName()) {
                $oldUsername = $user->username;
                $user->username = $socialiteUser->getName();
                $user->save();

                $ua = $request->header('User-Agent');
                $ip = $request->ip();
                $lang = $request->header('Accept-Language');

                LogEntry::create([
                    'user_id' => $user->id,
                    'model_id' => $user->id,
                    'model_type' => User::class,
                    'action' => 'modified user - changed user name from ' . $oldUsername . ' to ' . $socialiteUser->getName(),
                    'reason' => 'automatically detected changed user name when logging in',
                    'ip' => $ip,
                    'ua' => $ua . ' ' . $lang,
                    'protected' => LogEntry::LOG_PROTECTION_NONE,
                ]);
            }

            return $user;
        });

        Auth::login($user, true);
        return redirect()->intended('/');
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        return redirect('/');
    }

    private function guard()
    {
        return Auth::guard();
    }
}
