<?php

namespace App\MwApi;

use Mediawiki\Api\ApiUser;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\MediawikiFactory;
use Illuminate\Support\Facades\Log;

class MwApiGetter
{
    private static $loadedApis = [];

    public static function getApiForWiki(string $wiki): MediawikiApi
    {
        if (in_array($wiki, self::$loadedApis)) {
            return self::$loadedApis[$wiki];
        }

        $api = new MediawikiApi(MwApiUrls::getWikiUrl($wiki));
        if (config('wikis.login.username') && config('wikis.login.password')) {
            $api->login(new ApiUser(config('wikis.login.username'), config('wikis.login.password')));
        } else {
            Log::warning('Not logging in to MediaWiki, no credentials provided');
        }


        return self::$loadedApis[$wiki] = $api;
    }

    public static function getServicesForWiki(string $wiki): MediawikiFactory
    {
        return new MediawikiFactory(self::getApiForWiki($wiki));
    }
}
