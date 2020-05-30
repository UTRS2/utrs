<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Wikitask;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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

    public function callback()
    {
        $socialiteUser = Socialite::driver('mediawiki')->user();

        $user = User::firstOrCreate([
            'username' => $socialiteUser->name,
        ], [
            'password' => '',
            'wikis' => '',
            'verified' => true,
        ]);

        if (!$user->verified) {
            $user->verified = true;
            $user->save();
        }

        if ($user->wasRecentlyCreated) {
            Wikitask::create([
                'task' => 'verifyaccount',
                'actionid' => $user->id,
            ]);
        }

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
