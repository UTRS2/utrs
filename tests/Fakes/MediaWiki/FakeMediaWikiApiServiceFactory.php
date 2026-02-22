<?php

namespace Tests\Fakes\MediaWiki;

use MediaWiki\Api\MediaWikiFactory;
use Tests\Fakes\MediaWiki\Factories\FakeUserGetter;

class FakeMediaWikiApiServiceFactory extends MediaWikiFactory
{
    private $fakeApi;
    private $wiki;

    public function __construct(FakeMediaWikiApi $api)
    {
        parent::__construct($api->getAddWikiMediaWikiApi());
        $this->fakeApi = $api;
    }

    public function newUserGetter()
    {
        return new FakeUserGetter($this->fakeApi);
    }
}
