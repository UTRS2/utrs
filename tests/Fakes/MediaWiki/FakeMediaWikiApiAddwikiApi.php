<?php

namespace Tests\Fakes\MediaWiki;

use Mediawiki\Api\MediawikiApi;

class FakeMediaWikiApiAddwikiApi extends MediawikiApi
{
    private $api;

    public function __construct(FakeMediaWikiApi $api)
    {
        parent::__construct('https://example.invalid/w/api.php');
        $this->api = $api;
    }
}
