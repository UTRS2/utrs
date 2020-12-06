<?php

namespace Tests\Browser\Admin\Bans;

use App\Models\Ban;
use Tests\Traits\SetupDatabaseForTesting;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Traits\TestHasUsers;

class BanViewTest extends DuskTestCase
{
    use SetupDatabaseForTesting;
    use TestHasUsers;

    public function test_non_oversighter_cant_view_oversighted_bans()
    {
        Ban::factory()->create(['target' => 'Protected ban', 'is_protected' => true,]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->getTooladminUser())
                ->visit(route('admin.bans.list'))
                ->assertSee('ban target removed')
                ->assertDontSee('Protected ban');
        });
    }

    public function test_oversighter_can_view_oversighted_bans()
    {
        Ban::factory()->create(['target' => 'Protected ban', 'is_protected' => true,]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->getFunctionaryTooladminUser())
                ->visit(route('admin.bans.list'))
                ->assertSee('Protected ban');
        });
    }
}
