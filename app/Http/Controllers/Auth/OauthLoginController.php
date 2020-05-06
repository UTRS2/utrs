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
            'username' => $socialiteUser->name,
        ], [
            // these parameters will be filled when the user is created
            'password' => '',
            'verified' => true,
        ]);

        Auth::login($user, true);
        return redirect()->intended('/');
    }
}
