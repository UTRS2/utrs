<?php

/**
 * This is the UTRS file that can be used to configure supported wikis.
 */
return [
    'login' => [
        'username' => env('MEDIAWIKI_USERNAME', ''),
        'password' => env('MEDIAWIKI_PASSWORD', ''),
    ],

    'base_permissions' => [
        'appeal_view' => ['admin', 'staff', 'steward'],
        'appeal_handle' => ['admin', 'staff', 'steward'],
        'appeal_checkuser' => ['checkuser', 'staff', 'steward'],
    ],

    'globalwiki' => [
        'name' => 'Global locks/blocks',
        'api_url' => env('WIKI_URL_GLOBAL', 'https://meta.wikimedia.org/w/api.php'),
        'url_base' => 'https://meta.wikimedia.org/',
        'responding_user_title' => 'Wikimedia Steward',
        'hidden_from_appeal_wiki_list' => true,

        'permission_overrides' => [
            'appeal_view' => ['steward', 'staff'],
            'appeal_handle' => ['steward', 'staff'],
            'appeal_checkuser' => ['steward', 'staff'],
        ],
    ],

    'wikis' => [
        // IF YOU ARE REMOVING WIKIS: ensure FakeMediaWikiRepository has two existing test wikis!

        'enwiki' => [
            'name' => 'English Wikipedia',
            'api_url' => env('WIKI_URL_ENWIKI', 'https://en.wikipedia.org/w/api.php'),
            'url_base' => 'https://en.wikipedia.org/',
            'responding_user_title' => 'English Wikipedia Administrator',
            'appeal_list_page' => 'User:AmandaNP/UTRS Appeals',
        ],
        'ptwiki' => [
            'name' => 'Portuguese Wikipedia',
            'api_url' => env('WIKI_URL_PTWIKI', 'https://pt.wikipedia.org/w/api.php'),
            'url_base' => 'https://pt.wikipedia.org/',
            'responding_user_title' => 'Portuguese Wikipedia Administrator',
            'hidden_from_appeal_wiki_list' => true,
        ],
    ],
];
