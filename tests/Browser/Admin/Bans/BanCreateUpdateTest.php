<?php

namespace Tests\Browser\Admin\Bans;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Traits\TestHasUsers;

class BanCreateUpdateTest extends DuskTestCase
{
    use DatabaseMigrations;
    use TestHasUsers;

    public function test_non_tooladmin_cant_create_ban()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/changelang/en')
                ->loginAs($this->getUser())
                ->visit(route('admin.bans.create'))
                ->assertSee('403')
                ->assertDontSee('Add ban');
        });
    }

    public function test_tooladmin_can_create_and_modify_user_ban()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/changelang/en')
                ->loginAs($this->getTooladminUser())
                ->visit(route('admin.bans.create'))
                ->assertDontSee('403')
                ->type('target', 'UTRS banned user')
                ->type('reason', 'UTRS public ban reason')
                ->type('comment', 'UTRS private ban comment')
                ->press(__('generic.submit'))
                ->waitForText(__('admin.bans.edit.details'),2)
                ->waitForText('UTRS banned user',2)
                ->waitForText(__('admin.bans.indefinite'),2)
                ->waitForText('English Wikipedia',2)
                ->waitForText('Action: created, Reason: UTRS private ban comment',2)
                ->type('reason', 'Another reason.')
                ->click('[for=is_active-0]')
                ->press(__('generic.submit'))
                ->waitForText(__('admin.bans.unbanned'),2);
        });
    }
}
