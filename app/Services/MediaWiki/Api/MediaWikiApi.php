<?php

namespace App\Services\MediaWiki\Api;

use Mediawiki\Api\MediawikiApi as AddwikiMediaWikiApi;
use Mediawiki\Api\MediawikiFactory;

/**
 * Provides access to the api for the specified MediaWiki endpoint.
 */
interface MediaWikiApi
{
    public function getAddWikiMediaWikiApi(): AddwikiMediaWikiApi;
    public function getAddWikiServices(): MediawikiFactory;

    public function getMediaWikiExtras(): MediaWikiExtras;

    public function login(): void;
}
