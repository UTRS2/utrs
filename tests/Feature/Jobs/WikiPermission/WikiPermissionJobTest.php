<?php

namespace Tests\Feature\Jobs\WikiPermission;

use App\Jobs\WikiPermission\LoadLocalPermissionsJob;
use App\Services\MediaWiki\Api\MediaWikiRepository;
use App\User;
use Tests\CreatesApplication;
use Tests\Fakes\MediaWiki\FakeMediaWikiRepository;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WikiPermissionJobTest extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    /** @var FakeMediaWikiRepository */
    private $repository;

    /** @var int */
    private $ordinal = 1;

    protected function setUp(): void
    {
        parent::setUp();

        User::unsetEventDispatcher();
        $this->swapRepository();
    }

    private function swapRepository()
    {
        $this->repository = new FakeMediaWikiRepository();
        $this->app->instance(MediaWikiRepository::class, $this->repository);

        $this->ordinal = 1;
    }

    private function getUser($data = []): User
    {
        $testData = [
            'name' => 'Test user ' . $this->ordinal,
            'userid' => $this->ordinal,
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

        $this->ordinal += 1;
        $this->repository->addFakeUser('enwiki', $testData);
        return factory(User::class)->create(['username' => $testData['name']]);
    }

    private function checkIsFiltered($data, $expected)
    {
        $user = $this->getUser($data);

        $job = new LoadLocalPermissionsJob($user, 'enwiki');
        $job->handle();

        if ($expected) {
            $this->assertEquals('', $user->wikis);
        } else {
            $this->assertEquals('enwiki', $user->wikis);
        }
    }

    public function test_it_filters_out_users_with_not_enough_edits()
    {
        $this->checkIsFiltered(['editcount' => 3000], false);
        $this->checkIsFiltered(['editcount' => 3], true);
    }

    public function test_it_filters_out_users_who_are_not_sysops()
    {
        $this->checkIsFiltered(['groups' => ['user', 'sysop']], false);
        $this->checkIsFiltered(['groups' => ['user']], true);
    }

    public function test_it_filters_out_blocked_users()
    {
        $this->checkIsFiltered(['blocked' => false], false);
        $this->checkIsFiltered(['blocked' => true], true);
    }

    public function test_it_updates_wikis_on_user()
    {
        $otherWikiId = $this->repository->getSupportedTargets(false)[1];

        $user = $this->getUser();
        $user->wikis = $otherWikiId;
        $user->save();

        $wiki = $this->repository->getSupportedTargets(false)[0];
        $job = new LoadLocalPermissionsJob($user, $wiki);

        $job->updateDoesExist(true);
        $this->assertEquals($otherWikiId . ',' . $job->getPermissionWikiId(), $user->wikis);

        $job->updateDoesExist(false);
        $this->assertEquals($otherWikiId, $user->wikis);
    }
}
