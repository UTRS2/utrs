<?php

namespace Tests\Fakes\MediaWiki;

use MediaWiki\Api\MediaWikiApi;

class FakeMediaWikiApiAddwikiApi extends MediaWikiApi
{
    private $api;

    public function __construct(FakeMediaWikiApi $api)
    {
        parent::__construct('https://example.invalid/w/api.php');
        $this->api = $api;
    }
}
