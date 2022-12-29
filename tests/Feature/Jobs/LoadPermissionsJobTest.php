<?php

namespace Tests\Feature\Jobs;

use App\Jobs\LoadPermissionsJob;
use App\Services\MediaWiki\Api\MediaWikiRepository;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Fakes\MediaWiki\FakeMediaWikiRepository;
use Mediawiki\Api\MediawikiFactory;
use Tests\TestCase;
use Tests\Traits\TestHasUsers;

class LoadPermissionsJobTest extends TestCase
{
    use DatabaseMigrations;
    use TestHasUsers;

    public function test_it_adds_and_removes_groups() {
        $repository = new FakeMediaWikiRepository();
        $this->app->instance(MediaWikiRepository::class, $repository);

        $user = $this->getUser([
            'ptwiki' => ['user', 'admin', 'tooladmin'],
        ]);

        $repository->addFakeUser('enwiki', [
            'name' => $user->username,
            'groups' => ['user', 'sysop']
        ]);

        $repository->addFakeUser('ptwiki', [
            'name' => $user->username,
            'groups' => ['user']
        ]);

        $repository->addFakeUser('global', [
            'name' => $user->username,
            'groups' => []
        ]);

        $job = new LoadPermissionsJob($user);
        $job->handle($repository);

        $enwikiPermObject = $user->permissions->firstWhere('wiki', 'enwiki');
        $this->assertEquals(1, $enwikiPermObject->admin);
        $this->assertEquals(1, $enwikiPermObject->user);

        $ptwikiPermObject = $user->permissions->firstWhere('wiki', 'ptwiki');
        $this->assertEquals(0, $ptwikiPermObject->admin);
        // 'user' permission is directly dependant on any permissions we check automatically
        $this->assertEquals(0, $ptwikiPermObject->user);
        // is this wanted? we don't drop any advanced perms if user is not admin anymore
        $this->assertEquals(1, $ptwikiPermObject->tooladmin);
    }
}