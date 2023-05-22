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

    public function test_user_can_view_appeals_list()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/changelang/en')
                ->loginAs($this->getUser())
                ->visit('/review')
                ->assertSee('All unreserved open appeals')
                ->assertDontSee('403');
        });
    }
}
