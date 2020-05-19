<?php

namespace App\MwApi;

use Illuminate\Support\Str;

/**
 * Wrapper for storing and retrieving MediaWiki api endpoint URLs.
 * It also allows overriding them using .env on local development.
 */
class MwApiUrls
{
    public static function getWikiProperty(string $wiki, string $name, $default = null) {
        $prefix = 'wikis.wikis.' . Str::lower($wiki) . '.';

        if (Str::lower($wiki) === 'global' || $wiki === '*') {
            $prefix = 'wikis.globalwiki.';
        }

        return config($prefix . $name, $default);
    }

    /**
     * @return string api url for the global wiki (meta on wmf sites)
     */
    public static function getGlobalWikiUrl()
    {
        return self::getWikiProperty('*', 'api_url');
    }

    /**
     * @param string $wiki the wiki identifier to get the api url for
     * @return string api url for specified wiki
     */
    public static function getWikiUrl(string $wiki)
    {
        return self::getWikiProperty($wiki, 'api_url');
    }

    /**
     * @return array an array of individual wikis that have their api url available
     */
    public static function getSupportedWikis()
    {
        return array_keys(config('wikis.wikis'));
    }

    /**
     * @return array of id => name pairs of wikis that can have blocks appealed
     */
    public static function getWikiDropdown()
    {
        return collect(self::getSupportedWikis())
            ->push('global')
            ->mapWithKeys(function ($wiki) {
                return [$wiki => self::getWikiProperty($wiki, 'name')];
            })
            ->toArray();
    }
}

