<?php

namespace App\MwApi;

use Illuminate\Support\Str;

/**
 * Wrapper for storing and retrieving MediaWiki api endpoint URLs.
 * It also allows overriding them using .env on local development.
 */
class MwApiUrls
{
    /**
     * @return string api url for the global wiki (meta on wmf sites)
     */
    public static function getGlobalWikiUrl()
    {
        return config('wikis.globalwiki.api_url');
    }

    /**
     * @param string $wiki the wiki identifier to get the api url for
     * @return string api url for specified wiki
     */
    public static function getWikiUrl(string $wiki)
    {
        if (Str::lower($wiki) === 'global') {
            return self::getGlobalWikiUrl();
        }

        return config('wikis.wikis.' . Str::lower($wiki) . '.api_url');
    }

    /**
     * @return array an array of all wikis that have their api url available
     */
    public static function getSupportedWikis()
    {
        return array_keys(config('wikis.wikis'));
    }
}

