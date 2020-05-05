<?php

namespace App\Http;

use Illuminate\Support\Arr;
use Laravel\Socialite\Two\User;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;

class WikiSocialiteProvider extends AbstractProvider implements ProviderInterface
{
    protected $scopes = ['mwoauth-authonlyprivate'];

    private function getBaseUrl()
    {
        return env('OAUTH_BASE_URL');
    }

    protected function getAuthUrl($state)
    {
        $url = $this->getBaseUrl() . '/w/rest.php/oauth2/authorize';
        return $this->buildAuthUrlFromBase($url, $state);
    }

    protected function getTokenUrl()
    {
        return $this->getBaseUrl() . '/w/rest.php/oauth2/access_token';
    }

    protected function getUserByToken($token)
    {
        $url = $this->getBaseUrl() . '/w/rest.php/oauth2/resource/profile';
        $response = $this->getHttpClient()->get($url, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => Arr::get($user, 'id'), // TODO: this seems to not be present, needs investigation
            'email' => Arr::get($user, 'email'),
            'username' => Arr::get($user, 'username'),
            'groups' => Arr::get($user, 'groups'),
            'blocked' => Arr::get($user, 'blocked'),
        ]);
    }

    protected function getTokenFields($code)
    {
        return array_merge(
            parent::getTokenFields($code),
            ['grant_type' => 'authorization_code']
        );
    }
}