<?php

namespace Tests\Browser\Appeals;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Traits\TestHasUsers;

class AppealCreateTest extends DuskTestCase
{
    use DatabaseMigrations;
    use TestHasUsers;

    public function test_logged_in_user_cant_make_appeal()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/changelang/en')
                ->loginAs($this->getUser())
                ->visit('/')
                ->clickLink('Appeal my IP block')
                ->assertDontSee('What wiki are you blocked on?')
                ->assertSee('This action can only be performed by users who are not logged in.');
        });
    }

    public function test_can_create_account_block_appeal()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/changelang/en')
                ->visit('/')
                ->clickLink('Appeal my block')
                ->type('appealfor', 'Example')
                ->click('[for=blocktype-1]')
                ->type('appealtext', 'I did not do anything wrong! The admin is corrupt and if I\'m not unblocked, [hidden per WP:NLT]!')
                ->type('email', 'test@example.com')
                ->waitForText('Submit',2)
                ->press('Submit')
                ->assertSee('Your appeal is being processed');
        });
    }

    public function test_can_create_ip_block_appeal()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/changelang/en')
                ->visit('/')
                ->clickLink('Appeal my IP block')
                ->type('appealfor', '1.1.1.1')
                ->type('appealtext', 'Why did you only block me even thru [other editors name here] was also edit warring? This is unfair! I demand to talk to a supervisor!')
                ->type('email', 'test@example.com')
                ->press('Submit')
                ->assertSee('Your appeal is being processed');
        });
    }
}
