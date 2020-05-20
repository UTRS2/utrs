<?php

namespace Tests\Browser\Appeals;

use App\Appeal;
use Tests\Traits\TestHasUsers;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AppealCreateTest extends DuskTestCase
{
    use DatabaseMigrations;
    use TestHasUsers;

    public function test_can_create_account_block_appeal()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->clickLink('Appeal my block')
                ->type('appealfor', 'Example')
                ->click('[for=blocktype-1]')
                ->click('[for=privacyreview-0]')
                ->type('appealtext', 'I did not do anything wrong! The admin is corrupt and if I\'m not unblocked, [hidden per WP:NLT]!')
                ->press('Submit')
                ->assertSee('Do not lose this Appeal Key. You can only recover it if you have an account with an email address enabled.');
        });
    }

    public function test_can_create_ip_block_appeal()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->clickLink('Appeal IP block')
                ->type('appealfor', '1.1.1.1')
                ->click('[for=privacyreview-0]')
                ->type('appealtext', 'Why did you only block me even thru [other editors name here] was also edit warring? This is unfair! I demand to talk to a supervisor!')
                ->press('Submit')
                ->assertSee('Do not lose this Appeal Key. You can only recover it if you have an account with an email address enabled.');
        });
    }
}
