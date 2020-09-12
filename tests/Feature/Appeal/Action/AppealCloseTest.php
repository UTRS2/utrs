<?php

namespace Tests\Feature\Appeal\Action;

use App\Appeal;

class AppealCloseTest extends BaseAppealActionTest
{
    public function test_user_can_accept_appeal()
    {
        $user = $this->getUser();
        $appeal = factory(Appeal::class)->create([ 'status' => Appeal::STATUS_OPEN, ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.close', [ $appeal, Appeal::STATUS_ACCEPT ]));
        $response->assertRedirect();

        $appeal->refresh();
        $this->assertEquals(Appeal::STATUS_ACCEPT, $appeal->status);
        $this->assertTrue($appeal->comments()
            ->where('action', 'closed - accept')
            ->where('user', $user->id)
            ->exists());
    }

    public function test_user_can_decline_appeal()
    {
        $user = $this->getUser();
        $appeal = factory(Appeal::class)->create([ 'status' => Appeal::STATUS_OPEN, ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.close', [ $appeal, Appeal::STATUS_DECLINE ]));
        $response->assertRedirect();

        $appeal->refresh();
        $this->assertEquals(Appeal::STATUS_DECLINE, $appeal->status);
        $this->assertTrue($appeal->comments()
            ->where('action', 'closed - decline')
            ->where('user', $user->id)
            ->exists());
    }


    public function test_user_cant_use_whatever_statuses_when_closing()
    {
        $user = $this->getUser();
        $appeal = factory(Appeal::class)->create([ 'status' => Appeal::STATUS_OPEN, ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.close', [ $appeal, Appeal::STATUS_INVALID ]));
        $response->assertStatus(400);

        $appeal->refresh();
        $this->assertEquals(Appeal::STATUS_OPEN, $appeal->status);
        $this->assertFalse($appeal->comments()
            ->where('action', 'closed - accept')
            ->where('user', $user->id)
            ->exists());
    }

}
