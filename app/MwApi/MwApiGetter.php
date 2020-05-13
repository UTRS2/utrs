<?php

namespace App\MwApi;

use Mediawiki\Api\ApiUser;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\MediawikiFactory;

class MwApiGetter
{
    private static $loadedApis = [];

    public static function getApiForWiki(string $wiki): MediawikiApi
    {
        if (in_array($wiki, self::$loadedApis)) {
            return self::$loadedApis[$wiki];
        }

        $api = new MediawikiApi(MwApiUrls::getWikiUrl($wiki));
        $api->login(new ApiUser(config('wikis.login.username'), config('wikis.login.password')));

        return self::$loadedApis[$wiki] = $api;
    }

    public static function getServicesForWiki(string $wiki): MediawikiFactory
    {
        return new MediawikiFactory(self::getApiForWiki($wiki));
    }
}
