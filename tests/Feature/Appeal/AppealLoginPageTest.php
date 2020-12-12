<?php

namespace Tests\Feature\Appeal;

use App\Models\Appeal;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestHasUsers;

class AppealLoginPageTest extends TestCase
{
    use DatabaseMigrations;
    use TestHasUsers;

    public function test_admin_doesnt_see_login_page_when_viewing_appeal()
    {
        $appeal = Appeal::factory()->create();
        $user = $this->getUser();

        $response = $this
            ->actingAs($user)
            ->get(route('appeal.view', $appeal));

        $response
            ->assertSee('Appeal for ')
            ->assertDontSee('Authentication required');
    }

    public function test_logged_out_user_does_see_login_page()
    {
        $appeal = Appeal::factory()->create();

        $response = $this
            ->get(route('appeal.view', $appeal));

        $response
            ->assertDontSee('Appeal for ')
            ->assertSee('Authentication required');
    }
}
