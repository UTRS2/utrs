<?php

namespace Tests\Feature\Appeal\Action;

use App\Appeal;
use App\MwApi\MwApiUrls;

class AppealReserveReleaseTest extends BaseAppealActionTest
{
    public function test_user_can_reserve_appeal()
    {
        $user = $this->getUser();
        $appeal = factory(Appeal::class)->create([ 'handlingadmin' => null, ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.reserve', $appeal));
        $response->assertRedirect();

        $appeal->refresh();
        $this->assertEquals($user->id, $appeal->handlingadmin);
        $this->assertTrue($appeal->comments()
            ->where('action', 'reserve')
            ->where('user', $user->id)
            ->exists());
    }

    public function test_user_cant_reserve_already_reserved_appeal()
    {
        $user = $this->getUser();
        $reservedToUser = $this->getUser();
        $this->assertNotEquals($user->id, $reservedToUser->id);

        $appeal = factory(Appeal::class)->create([ 'handlingadmin' => $reservedToUser->id, ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.reserve', $appeal));
        $response->assertStatus(403);
        $response->assertSee("This appeal has already been reserved.");

        $appeal->refresh();
        $this->assertEquals($reservedToUser->id, $appeal->handlingadmin);
        $this->assertFalse($appeal->comments()
            ->where('action', 'reserve')
            ->where('user', $user->id)
            ->exists());
    }

    public function test_user_cant_reserve_appeal_that_they_cant_see()
    {
        $user = $this->getUser();
        $wiki = MwApiUrls::getSupportedWikis()[1];

        $appeal = factory(Appeal::class)->create([ 'wiki' => $wiki, 'handlingadmin' => null, ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.reserve', $appeal));
        $response->assertStatus(403);
        $response->assertSee("Only $wiki administrators are able to see this appeal.");

        $appeal->refresh();
        $this->assertNull($appeal->handlingadmin);
        $this->assertFalse($appeal->comments()
            ->where('action', 'reserve')
            ->where('user', $user->id)
            ->exists());
    }

    public function test_user_can_release_own_appeal()
    {
        $user = $this->getUser();
        $appeal = factory(Appeal::class)->create([ 'handlingadmin' => $user->id, ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.release', $appeal));
        $response->assertRedirect();

        $appeal->refresh();
        $this->assertNull($appeal->handlingadmin);
        $this->assertTrue($appeal->comments()
            ->where('action', 'release')
            ->where('user', $user->id)
            ->exists());
    }

    public function test_user_cant_release_other_appeal()
    {
        $user = $this->getUser();
        $reservedToUser = $this->getUser();
        $this->assertNotEquals($user->id, $reservedToUser->id);

        $appeal = factory(Appeal::class)->create([ 'handlingadmin' => $reservedToUser->id, ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.release', $appeal));
        $response->assertStatus(403);
        $response->assertSee("Only tool administrators can force release appeals.");

        $appeal->refresh();
        $this->assertEquals($reservedToUser->id, $appeal->handlingadmin);
        $this->assertFalse($appeal->comments()
            ->where('action', 'release')
            ->where('user', $user->id)
            ->exists());
    }

    public function test_tool_admin_can_release_other_appeal()
    {
        $user = $this->getTooladminUser();
        $reservedToUser = $this->getUser();
        $this->assertNotEquals($user->id, $reservedToUser->id);

        $appeal = factory(Appeal::class)->create([ 'handlingadmin' => $reservedToUser->id, ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.release', $appeal));
        $response->assertRedirect();

        $appeal->refresh();
        $this->assertNull($appeal->handlingadmin);
        $this->assertTrue($appeal->comments()
            ->where('action', 'release')
            ->where('user', $user->id)
            ->exists());
    }

    public function test_user_cant_release_appeal_they_cant_see()
    {
        $user = $this->getUser();
        $reservedToUser = $this->getUser([ 'ptwiki' => [ 'user', 'admin', ], ]);
        $this->assertNotEquals($user->id, $reservedToUser->id);

        $wiki = MwApiUrls::getSupportedWikis()[1];

        $appeal = factory(Appeal::class)->create([
            'wiki'          => $wiki,
            'handlingadmin' => $reservedToUser->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.release', $appeal));

        $response->assertStatus(403);
        $response->assertSee("Only $wiki administrators are able to see this appeal.");

        $appeal->refresh();
        $this->assertEquals($reservedToUser->id, $appeal->handlingadmin);
        $this->assertFalse($appeal->comments()
            ->where('action', 'reserve')
            ->where('user', $user->id)
            ->exists());
    }
}
