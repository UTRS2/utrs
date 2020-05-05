<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class OauthLoginController extends Controller
{
    public function login()
    {
        return Socialite::driver('wiki')
            ->redirect();
    }

    public function callback()
    {
        $socialiteUser = Socialite::driver('wiki')->user();

        $user = User::firstOrCreate([
            'username' => $socialiteUser->username,
        ], [
            // these parameters will be filled when the user is created
            'password' => '',
        ]);

        Auth::login($user, true);
        return redirect()->intended('/');
    }
}
