<?php

namespace Tests\Feature\Appeal\Action;

use App\Models\Appeal;
use App\Models\Privatedata;

class ActionAppealCheckUserTest extends BaseAppealActionTest
{
    public function test_normal_admin_cant_request_checkuser_information()
    {
        $user = $this->getUser();
        $appeal = Appeal::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.viewcheckuser', $appeal), ['reason' => 'Test example']);
        $response->assertStatus(403);

        $this->assertFalse($appeal->comments()
            ->where('action', 'checkuser')
            ->where('user_id', $user->id)
            ->exists());
    }

    public function test_normal_admin_cant_see_checkuser_information()
    {
        $user = $this->getUser();
        $appeal = Appeal::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('appeal.view', $appeal));

        $response->assertDontSee('The CU data for this appeal has expired.');
    }

    public function test_checkuser_can_request_checkuser_information()
    {
        $user = $this->getFunctionaryTooladminUser();
        $appeal = Appeal::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('appeal.action.viewcheckuser', $appeal), ['reason' => 'Test example']);
        $response->assertRedirect();

        $this->assertTrue($appeal->comments()
            ->where('action', 'checkuser')
            ->where('user_id', $user->id)
            ->exists());
    }

    public function test_checkuser_cant_see_checkuser_information_without_requesting()
    {
        $user = $this->getFunctionaryTooladminUser();
        $appeal = Appeal::factory()->create();

        Privatedata::factory()->create([
            'appeal_id' => $appeal->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('appeal.view', $appeal));

        $this->assertFalse($appeal->comments()
            ->where('action', 'checkuser')
            ->where('user_id', $user->id)
            ->exists());

        $response->assertSee('You have not submitted a request to view the CheckUser data yet');
    }

    public function test_checkuser_can_see_checkuser_information_after_requesting()
    {
        $user = $this->getFunctionaryTooladminUser();
        $appeal = Appeal::factory()->create();
        Privatedata::factory()->create([
            'appeal_id' => $appeal->id,
            'useragent' => 'Example string to look out for!!!',
        ]);

        $this->actingAs($user)
            ->post(route('appeal.action.viewcheckuser', $appeal), ['reason' => 'Test example']);

        $response = $this
            ->actingAs($user)
            ->get(route('appeal.view', $appeal));

        $response->assertSee('Example string to look out for!!!');
    }

}
