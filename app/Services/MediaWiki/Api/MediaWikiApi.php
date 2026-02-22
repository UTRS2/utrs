<?php

namespace App\Services\MediaWiki\Api;

use Addwiki\MediawikiApiBase\Client\Action\ActionApi;
use Addwiki\MediawikiApi\MediawikiFactory;

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