<?php

namespace Tests\Feature\Appeal\Action;

use App\Models\Appeal;

class AppealReferTest extends BaseAppealActionTest
{
    public function test_user_can_refer_an_appeal_to_tool_administrators()
    {
        $user = $this->getUser();
        $appeal = Appeal::factory()->create([ 'status' => Appeal::STATUS_OPEN, ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.tooladmin', $appeal));
        $response->assertRedirect();

        $appeal->refresh();
        $this->assertEquals(Appeal::STATUS_ADMIN, $appeal->status);
        $this->assertTrue($appeal->comments()
            ->where('action', 'sent for tool administrator review')
            ->where('user', $user->id)
            ->exists());
    }

    public function test_user_can_refer_an_appeal_to_checkusers()
    {
        $user = $this->getUser();
        $appeal = Appeal::factory()->create([ 'status' => Appeal::STATUS_OPEN, ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.checkuser', $appeal), [
                'cu_reason' => 'Example CheckUser reason',
            ]);
        $response->assertRedirect();

        $appeal->refresh();
        $this->assertEquals(Appeal::STATUS_CHECKUSER, $appeal->status);
        $this->assertTrue($appeal->comments()
            ->where('action', 'sent for CheckUser review')
            ->where('reason', 'Example CheckUser reason')
            ->where('user', $user->id)
            ->exists());
    }

    public function test_user_cant_refer_an_appeal_to_checkusers_without_a_reason()
    {
        $user = $this->getUser();
        $appeal = Appeal::factory()->create([ 'status' => Appeal::STATUS_OPEN, ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.checkuser', $appeal));
        $response->assertSessionHasErrors('cu_reason');

        $appeal->refresh();
        $this->assertEquals(Appeal::STATUS_OPEN, $appeal->status);
        $this->assertFalse($appeal->comments()
            ->where('action', 'sent for CheckUser review')
            ->where('user', $user->id)
            ->exists());
    }

    public function test_tool_admin_can_send_an_appeal_back_to_tool_users()
    {
        $user = $this->getTooladminUser();
        $appeal = Appeal::factory()->create([ 'status' => Appeal::STATUS_ADMIN, ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.reopen', $appeal));
        $response->assertRedirect();

        $appeal->refresh();
        $this->assertEquals(Appeal::STATUS_OPEN, $appeal->status);
        $this->assertTrue($appeal->comments()
            ->where('action', 're-open')
            ->where('user', $user->id)
            ->exists());
    }
}
