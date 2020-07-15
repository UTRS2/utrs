<?php

namespace Tests\Fakes\MediaWiki;

use App\Services\MediaWiki\Api\MediaWikiApi;
use App\Services\MediaWiki\Implementation\RealMediaWikiRepository;
use App\User;

class FakeMediaWikiRepository extends RealMediaWikiRepository
{
    private $fakeUsers = [];

    public function getSupportedTargets($includeGlobal = true): array
    {
        return $includeGlobal ? ['enwiki', 'ptwiki', 'global'] : ['enwiki', 'ptwiki'];
    }

    public function getApiForTarget(string $target): MediawikiApi
    {
        return new FakeMediaWikiApi($this, $target);
    }

    public function addFakeUser(string $wiki, array $data)
    {
        if (!array_key_exists($wiki, $this->fakeUsers)) {
            $this->fakeUsers[$wiki] = [];
        }

        $this->fakeUsers[$wiki][$data['name']] = $data;
    }

    public function getFakeUser(string $wiki, string $username)
    {
        if (!array_key_exists($wiki, $this->fakeUsers)) {
            return null;
        }

        return $this->fakeUsers[$wiki][$username] ?? null;
    }
}
