<?php

/**
 * This is the UTRS file that can be used to configure supported wikis.
 */
return [
    'login' => [
        'username' => env('MEDIAWIKI_USERNAME'),
        'password' => env('MEDIAWIKI_PASSWORD'),
    ],

    'globalwiki' => [
        'name' => 'Global locks/blocks',
        'api_url' => env('WIKI_URL_GLOBAL', 'https://meta.wikimedia.org/w/api.php'),
        'url_base' => 'https://meta.wikimedia.org/',
    ],

    'wikis' => [
        'enwiki' => [
            'name' => 'English Wikipedia',
            'api_url' => env('WIKI_URL_ENWIKI', 'https://en.wikipedia.org/w/api.php'),
            'url_base' => 'https://en.wikipedia.org/',
        ],
        'ptwiki' => [
            'name' => 'Portuguese Wikipedia',
            'api_url' => env('WIKI_URL_PTWIKI', 'https://pt.wikipedia.org/w/api.php'),
            'url_base' => 'https://pt.wikipedia.org/',
        ],
    ],
];
