<?php

namespace App\MediawikiIntegration;

use Illuminate\Support\Str;

/**
 * Wrapper for storing and retrieving MediaWiki api endpoint URLs.
 * It also allows overriding them using .env on local development.
 */
class WikiApiUrls
{
    public static function getGlobalWikiUrl()
    {
        return env('WIKI_URL_GLOBAL', 'https://meta.wikimedia.org/w/api.php');
    }

    public static function getWikiUrl($wiki)
    {
        if ($env = env('WIKI_URL_' . Str::upper($wiki), false)) {
            return $env;
        }

        return [
            'enwiki' => 'https://en.wikipedia.org/w/api.php',
            'ptwiki' => 'https://pt.wikipedia.org/w/api.php',
        ][$wiki];
    }
}
