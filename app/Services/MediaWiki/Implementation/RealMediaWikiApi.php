<?php

namespace App\Services\MediaWiki\Implementation;

use Throwable;
use RuntimeException;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\FileCookieJar;

use Addwiki\Mediawiki\Api\MediawikiFactory;
use Addwiki\Mediawiki\Api\CategoryLookupException;
use Addwiki\Mediawiki\Api\SimpleRequest;
use Addwiki\Mediawiki\Api\Client\Action\ActionApi;
use Addwiki\Mediawiki\Api\Client\Auth\NoAuth;
use Addwiki\Mediawiki\Api\Client\Action\Request\ActionRequest;

use App\Services\MediaWiki\Api\MediaWikiApi as MediaWikiApiContract;
use App\Services\MediaWiki\Api\MediaWikiExtras;
use App\Services\MediaWiki\Implementation\RealMediaWikiExtras;

class RealMediaWikiApi implements MediaWikiApiContract
{
    /** @var bool */
    private $loggedIn = false;

    /** @var MediawikiFactory */
    private $factory;

    /** @var ActionApi */
    private $api;

    /** @var string */
    private $apiUrl;

    /** @var Client */
    private $guzzleClient;

    /** @var bool */
    private $hasExistingSession = false;

    public function __construct(string $identifier, string $url)
    {
        $this->apiUrl = $url;

        $this->guzzleClient = $this->createGuzzleClient($identifier);

        if (config('wikis.login.username') && config('wikis.login.password')) {
            $auth = new \Addwiki\Mediawiki\Api\Client\Auth\UserAndPassword(
                config('wikis.login.username'),
                config('wikis.login.password')
            );
        }
        else {
            $auth = new NoAuth();
        }

        $this->api = new ActionApi(
            $this->apiUrl,
            $auth,
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

        $opts = [
            'cookies' => $cookieJar,
            'headers' => [
                'User-Agent' => 'UTRS 3, https://github.com/utrs2/utrs',
            ],
        ];

        if (config('wikis.login.username') && config('wikis.login.password')) {
            $opts['auth'] = [config('wikis.login.username'), config('wikis.login.password')];
        }

        return new Client($opts);
    }

    public function getAddwikiMediaWikiApi(): ActionApi
    {
        return $this->api;
    }

    public function getAddwikiServices(): MediawikiFactory
    {
        return $this->factory;
    }

    public function getMediaWikiExtras(): MediaWikiExtras
    {
        return new RealMediaWikiExtras($this);
    }

    public function login(): void
    {
        if ($this->loggedIn) {
            return;
        }

        if (!config('wikis.login.username') || !config('wikis.login.password')) {
            throw new RuntimeException('No MediaWiki API credentials located.');
        }

        $this->loggedIn = true;
        $this->hasExistingSession = true;
    }
}