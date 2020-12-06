<?php

namespace Tests\Browser;

use Tests\Traits\TestHasUsers;
use Tests\Traits\SetupDatabaseForTesting;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class HomePageTest extends DuskTestCase
{
    use SetupDatabaseForTesting;
    use TestHasUsers;

    public function test_home_page_renders()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee(config('app.name'));
        });
    }

    public function test_can_see_login_button()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSeeLink('Login')
                ->assertDontSeeLink('Go to Appeals');
        });
    }

    public function test_can_view_appeals_button()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->getUser())
                ->visit('/')
                ->assertDontSeeLink('Login')
                ->assertSeeLink('Go to Appeals');
        });
    }
}
