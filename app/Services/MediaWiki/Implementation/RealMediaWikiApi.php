<?php

namespace App\Services\MediaWiki\Implementation;

use Throwable;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Mediawiki\Api\SimpleRequest;
use GuzzleHttp\Cookie\FileCookieJar;
use App\Services\MediaWiki\Api\MediaWikiApi;
use App\Services\MediaWiki\Api\MediaWikiExtras;
use Mediawiki\Api\ApiUser;
use Mediawiki\Api\MediawikiApi as AddwikiMediaWikiApi;
use Mediawiki\Api\MediawikiFactory;
use RuntimeException;

class RealMediaWikiApi implements MediaWikiApi
{
    /** @var boolean */
    private $loggedIn = false;

    /** @var AddwikiMediaWikiApi */
    private $api;

    /** @var Client */
    private $guzzleClient;

    /** @var boolean */
    private $hasExistingSession = false;

    public function __construct(string $identifier, string $url)
    {
        $this->guzzleClient = $this->createGuzzleClient($identifier);
        $this->api = new AddwikiMediaWikiApi($url, $this->guzzleClient);

        /** @var CookieJar $jar */
        $jar = $this->guzzleClient->getConfig('cookies');
        if ($jar->getCookieByName('mediawiki_session')) {
            $this->hasExistingSession = true;
        }
    }

    /**
     * Create a Guzzle client with a cookie jar based on given wiki identifier
     */
    protected function createGuzzleClient(string $identifier): Client
    {
        $cookieJar = new FileCookieJar(storage_path('app/mw-cookies/' . $identifier . '.json'), true);
        return new Client([
            'cookies' => $cookieJar,
            'headers' => [
                'User-Agent' => 'UTRS 2, https://github.com/utrs2/utrs',
            ]
        ]);
    }

    public function getAddWikiMediaWikiApi(): AddwikiMediaWikiApi
    {
        return $this->api;
    }

    public function getAddWikiServices(): MediawikiFactory
    {
        return new MediawikiFactory($this->api);
    }

    public function getMediaWikiExtras(): MediaWikiExtras
    {
        return new RealMediaWikiExtras($this);
    }

    public function login()
    {
        if ($this->loggedIn) {
            return;
        }

        if ($this->hasExistingSession) {
            try {
                $userInfoResponse = $this->api->getRequest(new SimpleRequest('query', ['meta' => 'userinfo']));

                // mediawiki assigns user ID 0 to logged-out editors, so we can use that to check if we are logged in
                if ($userInfoResponse['query']['userinfo']['id'] > 0) {
                    $this->loggedIn = true;
                    return;
                }
            } catch (Throwable $ignored) {
                // we're checking if our session is invalid, it may just well throw an exception if it isn't
                // but we don't need to handle it - we'll clear all cookies next in any case
            }

            // looks like our session has expired, let's just kill it
            /** @var CookieJar $jar */
            $jar = $this->guzzleClient->getConfig('cookies');
            $jar->clear();

            $this->hasExistingSession = false;
        }

        if (config('wikis.login.username') && config('wikis.login.password')) {
            $this->api->login(new ApiUser(config('wikis.login.username'), config('wikis.login.password')));
            $this->loggedIn = true;
            $this->hasExistingSession = true;
            return;
        }

        throw new RuntimeException('No MediaWiki API credentials located.');
    }
}
