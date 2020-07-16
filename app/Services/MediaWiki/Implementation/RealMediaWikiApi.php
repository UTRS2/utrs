<?php

namespace App\Services\MediaWiki\Implementation;

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

    public function __construct(string $url)
    {
        $this->api = new AddwikiMediaWikiApi($url);
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

    public function login(bool $skipOnTesting = false)
    {
        if ($this->loggedIn) {
            return;
        }

        if (config('wikis.login.username') && config('wikis.login.password')) {
            $this->api->login(new ApiUser(config('wikis.login.username'), config('wikis.login.password')));
            $this->loggedIn = true;
            return;
        }

        if ($skipOnTesting && app()->environment('testing')) {
            return;
        }

        throw new RuntimeException('No MediaWiki API credentials located.');
    }
}
