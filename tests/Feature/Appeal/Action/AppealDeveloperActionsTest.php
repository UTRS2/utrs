<?php

namespace Tests\Feature\Appeal\Action;

use App\Models\Appeal;
use App\Jobs\GetBlockDetailsJob;
use Queue;

class AppealDeveloperActionsTest extends BaseAppealActionTest
{
    public function test_user_cant_invalidate_appeal()
    {
        $user = $this->getUser();
        $appeal = Appeal::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.invalidate', $appeal));
        $response->assertStatus(403);
        $response->assertSee('Only developers can take developer actions on appeals.');

        $appeal->refresh();
        $this->assertEquals(Appeal::STATUS_OPEN, $appeal->status);
        $this->assertFalse($appeal->comments()
            ->where('action', 'closed as invalid')
            ->where('user', $user->id)
            ->exists());
    }

    public function test_developer_can_invalidate_appeal()
    {
        $user = $this->getDeveloperUser();
        $appeal = Appeal::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.invalidate', $appeal));
        $response->assertRedirect();

        $appeal->refresh();
        $this->assertEquals(Appeal::STATUS_INVALID, $appeal->status);
        $this->assertTrue($appeal->comments()
            ->where('action', 'closed as invalid')
            ->where('user', $user->id)
            ->exists());
    }

    public function test_user_cant_reverify_appeal()
    {
        Queue::fake();

        $user = $this->getUser();
        $appeal = Appeal::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.findagain', $appeal));
        $response->assertStatus(403);
        $response->assertSee('Only developers can take developer actions on appeals.');

        $appeal->refresh();
        Queue::assertNothingPushed();
        $this->assertFalse($appeal->comments()
            ->where('action', 're-verify block details')
            ->where('user', $user->id)
            ->exists());
    }

    public function test_developer_cant_reverify_open_appeal()
    {
        Queue::fake();

        $user = $this->getDeveloperUser();
        $appeal = Appeal::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.findagain', $appeal));
        $response->assertStatus(403);
        $response->assertSee('Block details for this appeal have already been found.');

        $appeal->refresh();
        Queue::assertNothingPushed();
        $this->assertFalse($appeal->comments()
            ->where('action', 're-verify block details')
            ->where('user', $user->id)
            ->exists());
    }

    public function test_developer_can_reverify_appeal()
    {
        Queue::fake();

        $user = $this->getDeveloperUser();
        $appeal = Appeal::factory()->create([ 'status' => Appeal::STATUS_NOTFOUND, ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.findagain', $appeal));
        $response->assertRedirect();

        $appeal->refresh();
        Queue::assertPushed(GetBlockDetailsJob::class);
        $this->assertTrue($appeal->comments()
            ->where('action', 're-verify block details')
            ->where('user', $user->id)
            ->exists());
    }


}
