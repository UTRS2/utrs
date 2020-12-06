<?php

namespace Tests\Browser\Appeals;

use Tests\Traits\TestHasUsers;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Traits\SetupDatabaseForTesting;

class AppealListTest extends DuskTestCase
{
    use SetupDatabaseForTesting;
    use TestHasUsers;

    public function test_user_can_view_appeals_list()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->getUser())
                ->visit('/review')
                ->assertSee('All unreserved open appeals')
                ->assertDontSee('403');
        });
    }
}
