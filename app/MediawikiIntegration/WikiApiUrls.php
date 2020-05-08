<?php

namespace App\MediawikiIntegration;

use Illuminate\Support\Str;

/**
 * Wrapper for storing and retrieving MediaWiki api endpoint URLs.
 * It also allows overriding them using .env on local development.
 */
class WikiApiUrls
{
    const WIKI_URLS = [
        'enwiki' => 'https://en.wikipedia.org/w/api.php',
        'ptwiki' => 'https://pt.wikipedia.org/w/api.php',
    ];

    /**
     * @return string api url for the global wiki (meta on wmf sites)
     */
    public static function getGlobalWikiUrl()
    {
        return env('WIKI_URL_GLOBAL', 'https://meta.wikimedia.org/w/api.php');
    }

    /**
     * @param string $wiki the wiki identifier to get the api url for
     * @return string api url for specified wiki
     */
    public static function getWikiUrl(string $wiki)
    {
        if ($env = env('WIKI_URL_' . Str::upper($wiki), false)) {
            return $env;
        }

        return self::WIKI_URLS[$wiki];
    }

    /**
     * @return array an array of all wikis that have their api url available
     */
    public static function getSupportedWikis()
    {
        return array_keys(self::WIKI_URLS);
    }
}
