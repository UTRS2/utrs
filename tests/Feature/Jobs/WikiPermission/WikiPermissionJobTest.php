<?php

namespace Tests\Feature\Jobs\WikiPermission;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Jobs\WikiPermission\LoadLocalPermissionsJob;
use App\Services\MediaWiki\Api\MediaWikiRepository;
use Tests\Fakes\MediaWiki\FakeMediaWikiRepository;
use Tests\TestCase;

class WikiPermissionJobTest extends TestCase
{
    use DatabaseMigrations;

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
        $user = $this->repository->addFakeUser('enwiki', $data);
        return User::factory()->create(['username' => $user['name']]);
    }

    /**
     * @param $data array user data to override
     * @param $expected bool true if this user should be filtered out, false otherwise
     */
    private function checkIsFiltered($data, $expected)
    {
        $user = $this->getUser($data);

        $job = new LoadLocalPermissionsJob($user, 'enwiki');
        $job->handle();

        $isUser = $user->hasAnySpecifiedLocalOrGlobalPerms('enwiki', 'user');
        $this->assertEquals(!$expected, $isUser);
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
}
