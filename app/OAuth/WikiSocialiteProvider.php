<?php

namespace App\OAuth;

use Laravel\Socialite\One\User;
use Laravel\Socialite\One\AbstractProvider;

class WikiSocialiteProvider extends AbstractProvider
{
    public function user()
    {
        $user = $this->server->getUserDetails($token = $this->getToken(), $this->shouldBypassCache($token->getIdentifier(), $token->getSecret()));
        $data = [
            'name' => $user->username,
            'groups' => $user->groups,
        ];

        return (new User())->setRaw($data)->map($data);
    }
}
