<?php

namespace App\Http\Controllers\Auth;

use App\Utils\Logging\RequestLogContext;
use Exception;
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
            $socialiteUser = Socialite::driver('mediawiki')->user();

            // sanity check
            if (!$socialiteUser->getId() || !$socialiteUser->getName()) {
                throw new Exception('OAuth failed: MediaWiki api returned an invalid user object ' . json_encode($socialiteUser));
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

                $user->addLog(
                    new RequestLogContext($request),
                    'modified user - changed user name from ' . $oldUsername . ' to ' . $socialiteUser->getName(),
                    'automatically detected changed user name when logging in'
                );
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
