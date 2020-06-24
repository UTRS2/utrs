<?php

namespace Tests\Browser\Appeals;

use Tests\Traits\TestHasUsers;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AppealListTest extends DuskTestCase
{
    use DatabaseMigrations;
    use TestHasUsers;

    public function test_user_needs_to_be_verified_to_access_appeals_list()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->getUser([], ['verified' => 0]))
                ->visit('/review')
                ->assertSee('403')
                ->assertSee('Your account has not been verified yet.')
                ->assertDontSee('Current appeals');
        });
    }

    public function test_verified_user_can_view_appeals_list()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->getUser())
                ->visit('/review')
                ->assertSee('All unreserved open appeals')
                ->assertDontSee('403');
        });
    }
}
