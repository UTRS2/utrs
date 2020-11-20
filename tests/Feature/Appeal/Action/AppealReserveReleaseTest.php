<?php

namespace Tests\Feature\Appeal\Action;

use App\Models\Appeal;
use Illuminate\Support\Facades\Config;
use App\Services\Facades\MediaWikiRepository;

class AppealReserveReleaseTest extends BaseAppealActionTest
{
    public function test_user_can_reserve_appeal()
    {
        $user = $this->getUser();
        $appeal = Appeal::factory()->create([ 'handlingadmin' => null, ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.reserve', $appeal));
        $response->assertRedirect();

        $appeal->refresh();
        $this->assertEquals($user->id, $appeal->handlingadmin);
        $this->assertTrue($appeal->comments()
            ->where('action', 'reserve')
            ->where('user_id', $user->id)
            ->exists());
    }

    public function test_user_cant_reserve_already_reserved_appeal()
    {
        $user = $this->getUser();
        $reservedToUser = $this->getUser();
        $this->assertNotEquals($user->id, $reservedToUser->id);

        $appeal = Appeal::factory()->create([ 'handlingadmin' => $reservedToUser->id, ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.reserve', $appeal));
        $response->assertStatus(403);
        $response->assertSee("This appeal has already been reserved.");

        $appeal->refresh();
        $this->assertEquals($reservedToUser->id, $appeal->handlingadmin);
        $this->assertFalse($appeal->comments()
            ->where('action', 'reserve')
            ->where('user_id', $user->id)
            ->exists());
    }

    public function test_user_cant_reserve_appeal_that_they_cant_see()
    {
        $user = $this->getUser();
        $wiki = MediaWikiRepository::getSupportedTargets()[1];

        $appeal = Appeal::factory()->create([ 'wiki' => $wiki, 'handlingadmin' => null, ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.reserve', $appeal));
        $response->assertStatus(403);
        $response->assertSee('Viewing ' . $wiki . ' appeals is restricted to users in the following groups: admin');

        $appeal->refresh();
        $this->assertNull($appeal->handlingadmin);
        $this->assertFalse($appeal->comments()
            ->where('action', 'reserve')
            ->where('user_id', $user->id)
            ->exists());
    }


    public function test_user_cant_reserve_appeal_that_they_cant_take_actions_on()
    {
        Config::set('wikis.wikis.enwiki.permission_overrides.appeal_handle', ['checkuser']);

        $user = $this->getUser();
        $wiki = MediaWikiRepository::getSupportedTargets()[0];

        $appeal = Appeal::factory()->create([ 'wiki' => $wiki, 'handlingadmin' => null, ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.reserve', $appeal));
        $response->assertStatus(403);
        $response->assertSee('You can not take actions on this appeal.');

        $appeal->refresh();
        $this->assertNull($appeal->handlingadmin);
        $this->assertFalse($appeal->comments()
            ->where('action', 'reserve')
            ->where('user_id', $user->id)
            ->exists());
    }

    public function test_user_can_release_own_appeal()
    {
        $user = $this->getUser();
        $appeal = Appeal::factory()->create([ 'handlingadmin' => $user->id, ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.release', $appeal));
        $response->assertRedirect();

        $appeal->refresh();
        $this->assertNull($appeal->handlingadmin);
        $this->assertTrue($appeal->comments()
            ->where('action', 'release')
            ->where('user_id', $user->id)
            ->exists());
    }

    public function test_user_cant_release_other_appeal()
    {
        $user = $this->getUser();
        $reservedToUser = $this->getUser();
        $this->assertNotEquals($user->id, $reservedToUser->id);

        $appeal = Appeal::factory()->create([ 'handlingadmin' => $reservedToUser->id, ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.release', $appeal));
        $response->assertStatus(403);
        $response->assertSee("Only tool administrators can force release appeals.");

        $appeal->refresh();
        $this->assertEquals($reservedToUser->id, $appeal->handlingadmin);
        $this->assertFalse($appeal->comments()
            ->where('action', 'release')
            ->where('user_id', $user->id)
            ->exists());
    }

    public function test_tool_admin_can_release_other_appeal()
    {
        $user = $this->getTooladminUser();
        $reservedToUser = $this->getUser();
        $this->assertNotEquals($user->id, $reservedToUser->id);

        $appeal = Appeal::factory()->create([ 'handlingadmin' => $reservedToUser->id, ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.release', $appeal));
        $response->assertRedirect();

        $appeal->refresh();
        $this->assertNull($appeal->handlingadmin);
        $this->assertTrue($appeal->comments()
            ->where('action', 'release')
            ->where('user_id', $user->id)
            ->exists());
    }

    public function test_user_cant_release_appeal_they_cant_see()
    {
        $user = $this->getUser();
        $reservedToUser = $this->getUser([ 'ptwiki' => [ 'user', 'admin', ], ]);
        $this->assertNotEquals($user->id, $reservedToUser->id);

        $wiki = MediaWikiRepository::getSupportedTargets()[1];

        $appeal = Appeal::factory()->create([
            'wiki'          => $wiki,
            'handlingadmin' => $reservedToUser->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.release', $appeal));

        $response->assertStatus(403);
        $response->assertSee('Viewing ' . $wiki . ' appeals is restricted to users in the following groups: admin');

        $appeal->refresh();
        $this->assertEquals($reservedToUser->id, $appeal->handlingadmin);
        $this->assertFalse($appeal->comments()
            ->where('action', 'reserve')
            ->where('user_id', $user->id)
            ->exists());
    }
}
