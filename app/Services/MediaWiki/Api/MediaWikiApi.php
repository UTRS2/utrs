<?php

namespace App\Services\MediaWiki\Api;

use Addwiki\MediaWiki\Api\Client\Action\ActionApi;
use Addwiki\MediaWiki\Api\MediaWikiFactory;

/**
 * Provides access to the API for the specified MediaWiki endpoint.
 */
interface MediaWikiApi
{
    public function getAddwikiMediaWikiApi(): ActionApi;

    public function getAddwikiServices(): MediaWikiFactory;

    public function getMediaWikiExtras(): MediaWikiExtras;

    public function login(): void;
}