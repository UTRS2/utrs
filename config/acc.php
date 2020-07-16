<?php

/**
 * UTRS configuration file related to integration with ACC
 * see https://github.com/enwikipedia-acc/waca for information about ACC
 */
return [
    // controls which wikis can use ACC integration
    'enabled_for_wikis' => ['enwiki'],

    'max_sizes_to_appeal' => [
        'ipv4' => 18,
        'ipv6' => 40,
    ],
];
