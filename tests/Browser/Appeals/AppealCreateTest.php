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
                ->type('appealtext', 'I did not do anything wrong! The admin is corrupt and if I\'m not unblocked, I will be MAD!')
                ->press('Submit')
                ->assertSee('Do not lose this Appeal Key. You can only recover it if you have an account with an email address enabled.');
        });
    }
}
