<?php

namespace App\Services\MediaWiki\Implementation;

use Throwable;
use RuntimeException;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\FileCookieJar;

use Addwiki\Mediawiki\Api\MediawikiFactory;
use Addwiki\Mediawiki\Api\CategoryLookupException;
use Addwiki\Mediawiki\Api\ApiUser;
use Addwiki\Mediawiki\Api\SimpleRequest;
use Addwiki\Mediawiki\Api\Client\Action\ActionApi;
use Addwiki\Mediawiki\Api\Client\Auth\NoAuth;

use App\Services\MediaWiki\Api\MediaWikiApi as MediawikiApiContract;
use App\Services\Mediawiki\Api\MediaWikiExtras;
use App\Services\Mediawiki\Implementation\RealMediaWikiExtras;

class RealMediaWikiApi implements MediawikiApiContract
{
    /** @var bool */
    private $loggedIn = false;

    /** @var MediawikiFactory */
    private $factory;

    /** @var ActionApi */
    private $api;

    /** @var Client */
    private $guzzleClient;

    /** @var bool */
    private $hasExistingSession = false;

    public function __construct(string $identifier, string $url)
    {
        $this->guzzleClient = $this->createGuzzleClient($identifier);

        // Build ActionApi (3.x) and then the MediawikiFactory from it.
        $this->api = new ActionApi(
            $url,
            new NoAuth(),
            $this->guzzleClient
        );

        $this->factory = new MediawikiFactory($this->api);

        /** @var CookieJar $jar */
        $jar = $this->guzzleClient->getConfig('cookies');

        // Session names are unreliable; assume there is at least some session if the jar is not empty.
        if ($jar->count() > 0) {
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
            ],
        ]);
    }

    public function getAddwikiMediawikiApi(): ActionApi
    {
        return $this->api;
    }

    public function getAddwikiServices(): MediawikiFactory
    {
        return $this->factory;
    }

    public function getMediaWikiExtras(): MediawikiExtras
    {
        return new RealMediaWikiExtras($this);
    }

    public function login(): void
    {
        if ($this->loggedIn) {
            return;
        }

        if ($this->hasExistingSession) {
            try {
                $userInfoResponse = $this->api->getRequest(
                    new SimpleRequest('query', ['meta' => 'userinfo'])
                );

                // MediaWiki assigns user ID 0 to logged-out editors, so use that to check if we are logged in.
                if (($userInfoResponse['query']['userinfo']['id'] ?? 0) > 0) {
                    $this->loggedIn = true;
                    return;
                }
            } catch (Throwable $ignored) {
                // If session is invalid, MW may throw; we clear cookies next anyway.
            }

            // Looks like our session has expired; kill it.
            /** @var CookieJar $jar */
            $jar = $this->guzzleClient->getConfig('cookies');
            $jar->clear();

            $this->hasExistingSession = false;
        }

        if (config('wikis.login.username') && config('wikis.login.password')) {
            $this->api->login(
                new ApiUser(
                    config('wikis.login.username'),
                    config('wikis.login.password')
                )
            );

            $this->loggedIn = true;
            $this->hasExistingSession = true;
            return;
        }

        throw new RuntimeException('No MediaWiki API credentials located.');
    }
}