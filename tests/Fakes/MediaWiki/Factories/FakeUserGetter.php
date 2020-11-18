<?php

namespace Tests\Fakes\MediaWiki\Factories;

use Mediawiki\Api\Service\UserGetter;
use ReflectionObject;
use Tests\Fakes\MediaWiki\FakeMediaWikiApi;

class FakeUserGetter extends UserGetter
{
    private $fakeApi;

    public function __construct(FakeMediaWikiApi $api)
    {
        parent::__construct($api->getAddWikiMediaWikiApi());
        $this->fakeApi = $api;
    }

    public function getFromUsername($username)
    {
        $reflector = new ReflectionObject($this);
        $method = $reflector->getMethod('newUserFromListUsersResult');
        $method->setAccessible(true);
        return $method->invoke($this, $this->fakeApi->getRepository()->getFakeUser($this->fakeApi->getWikiName(), $username));
    }
}
