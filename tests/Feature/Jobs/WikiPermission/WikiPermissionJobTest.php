<?php

namespace Tests\Feature\Jobs\WikiPermission;

use Database\Factories\UserFactory;
use Mockery;
use App\User;
use ReflectionClass;
use App\MwApi\MwApiUrls;
use Mediawiki\DataModel\User as MediawikiUser;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Jobs\WikiPermission\LoadLocalPermissionsJob;

class WikiPermissionJobTest extends TestCase
{
    use RefreshDatabase;

    private function getUser($name = 'Admin')
    {
        User::unsetEventDispatcher();
        return User::factory()->create([
            'username' => $name,
        ]);
    }

    private function getMediawikiUser($name = 'Admin', $editCount = 3000, $groups = ['user', 'sysop'])
    {
        return Mockery::mock(MediawikiUser::class, function ($mock) use ($name, $editCount, $groups) {
            $mock->shouldReceive('getEditcount')
                ->andReturn($editCount);
            $mock->shouldReceive('getName')
                ->andReturn($name);
            $mock->shouldReceive('getGroups')
                ->andReturn($groups);
        });
    }

    public function test_it_filters_out_users_with_less_than_500_edits()
    {
        $user = $this->getUser();

        $job = Mockery::mock(LoadLocalPermissionsJob::class, [$user, MwApiUrls::getSupportedWikis()[0]], function ($mock) {
            $mock->shouldReceive('checkIsBlocked')
                ->andReturn(false);
        })
            ->makePartial();

        $groups = ['user', 'sysop'];

        $reflection = new ReflectionClass(get_class($job));
        $method = $reflection->getMethod('validateToolUserPermission');
        $method->setAccessible(true);

        $newUser = $this->getMediawikiUser($user->name, 30);
        $newUserGroups = $method->invokeArgs($job, [$newUser, $groups]);
        $this->assertEquals(['sysop'], $newUserGroups, 'user with less than 500 edits should not have group "user"');

        $oldUser = $this->getMediawikiUser($user->name, 3000);
        $oldUserGroups = $method->invokeArgs($job, [$oldUser, $groups]);
        $this->assertEquals(['user', 'sysop'], $oldUserGroups, 'user with more than 500 edits should have group "user"');
    }

    public function test_it_filters_user_out_without_sysop()
    {
        $user = $this->getUser();

        $job = Mockery::mock(LoadLocalPermissionsJob::class, [$user, MwApiUrls::getSupportedWikis()[0]], function ($mock) {
            $mock->shouldReceive('checkIsBlocked')
                ->andReturn(false);
        })
            ->makePartial();

        $groups = ['user'];

        $reflection = new ReflectionClass(get_class($job));
        $method = $reflection->getMethod('validateToolUserPermission');
        $method->setAccessible(true);

        $nonAdminUser = $this->getMediawikiUser($user->name, 12345);
        $newUserGroups = $method->invokeArgs($job, [$nonAdminUser, $groups]);
        $this->assertEquals([], $newUserGroups, 'user that is not administrator should not have group "user"');
    }

    public function test_it_filters_out_blocked_users()
    {
        $user = $this->getUser();

        $job = Mockery::mock(LoadLocalPermissionsJob::class, [$user, MwApiUrls::getSupportedWikis()[0]], function ($mock) {
            $mock->shouldReceive('checkIsBlocked')
                ->andReturn(true);
        })
            ->makePartial();

        $groups = ['user', 'sysop'];

        $reflection = new ReflectionClass(get_class($job));
        $method = $reflection->getMethod('validateToolUserPermission');
        $method->setAccessible(true);

        $blockedUser = $this->getMediawikiUser($user->name, 3000);
        $newUserGroups = $method->invokeArgs($job, [$blockedUser, $groups]);
        $this->assertEquals(['sysop'], $newUserGroups, 'blocked user should not have group "user"');
    }

    public function test_it_updates_wikis_on_user()
    {
        $otherWikiId = MwApiUrls::getSupportedWikis()[1];

        $user = $this->getUser();
        $user->wikis = MwApiUrls::getSupportedWikis()[1];
        $user->save();

        $wiki = MwApiUrls::getSupportedWikis()[0];

        $job = new LoadLocalPermissionsJob($user, $wiki);

        $job->updateDoesExist(true);
        $this->assertEquals($otherWikiId . ',' . $job->getPermissionWikiId(), $user->wikis);

        $job->updateDoesExist(false);
        $this->assertEquals($otherWikiId, $user->wikis);
    }
}
