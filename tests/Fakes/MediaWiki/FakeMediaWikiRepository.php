<?php

namespace Tests\Fakes\MediaWiki;

use App\Services\MediaWiki\Api\MediaWikiApi;
use App\Services\Facades\MediaWikiRepository;
use App\Services\MediaWiki\Implementation\RealMediaWikiRepository;

/**
 * Fake implementation of {@link MediaWikiRepository}
 */
class FakeMediaWikiRepository extends RealMediaWikiRepository
{
    /** @var int */
    private $testUserOrdinal = 1;

    /** @var array */
    private $fakeUsers = [];

    public function getSupportedTargets($includeGlobal = true): array
    {
        return $includeGlobal ? ['enwiki', 'ptwiki', 'global'] : ['enwiki', 'ptwiki'];
    }

    public function getApiForTarget(string $target): MediawikiApi
    {
        return new FakeMediaWikiApi($this, $target);
    }

    /**
     * Create a fake user in the specified wiki and return data for that specified user.
     *
     * @param string $wiki Wiki to create this user in
     * @param array $data New data for the user. Values omitted will be filled as defaults. See implementation for allowed parameters.
     * @return array $data merged with defaults
     */
    public function addFakeUser(string $wiki, array $data): array
    {
        if (!array_key_exists($wiki, $this->fakeUsers)) {
            $this->fakeUsers[$wiki] = [];
        }

        $testData = [
            'name' => 'Test user ' . $this->testUserOrdinal,
            'userid' => $this->testUserOrdinal,
            'groups' => ['user', 'sysop'],
            'implicitgroups' => ['autoconfirmed'],
            'blocked' => false,
            'editcount' => 1000,
            'registration' => '2020-01-01 01:01:01',
            'rights' => [],
            'gender' => 'unknown',
        ];

        foreach ($data as $key => $value) {
            $testData[$key] = $value;
        }

        $this->testUserOrdinal += 1;
        $this->fakeUsers[$wiki][$testData['name']] = $testData;
        return $testData;
    }

    public function getFakeUser(string $wiki, string $username)
    {
        if (!array_key_exists($wiki, $this->fakeUsers)) {
            return null;
        }

        return $this->fakeUsers[$wiki][$username] ?? null;
    }
}
