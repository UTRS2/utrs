<?php

namespace App\Services\MediaWiki\Api;

use Addwiki\Mediawiki\Api\Client\Action\ActionApi;
use Addwiki\Mediawiki\Api\MediawikiFactory;

/**
 * Provides access to the API for the specified MediaWiki endpoint.
 */
interface MediaWikiApi
{
    public function getAddwikiMediawikiApi(): ActionApi;

    public function getAddwikiServices(): MediawikiFactory;

    public function getMediaWikiExtras(): MediaWikiExtras;

    public function login(): void;
}