<?php

namespace Tests\Feature\Appeal\Action;

use App\Models\Appeal;

/**
 * @covers \App\Http\Controllers\AppealController::comment
 */
class AppealCommentTest extends BaseAppealActionTest
{
    public function testCanAddPrivateComment()
    {
        $user = $this->getUser();
        $appeal = Appeal::factory()->create([ 'status' => Appeal::STATUS_OPEN, ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.comment', $appeal), [
                'comment' => 'example comment',
            ]);
        $response->assertRedirect(route('appeal.view', $appeal));

        $appeal->refresh();
        $this->assertEquals(Appeal::STATUS_OPEN, $appeal->status);
        $this->assertTrue($appeal->comments()
            ->where('action', 'comment')
            ->where('user_id', $user->id)
            ->where('reason', 'example comment')
            ->exists());
    }
}
