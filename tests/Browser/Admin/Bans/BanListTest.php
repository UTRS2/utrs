<?php

namespace Tests\Browser\Admin\Bans;

use App\Models\Ban;
use Carbon\Carbon;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Traits\TestHasUsers;
use Tests\Traits\SetupDatabaseForTesting;

class BanListTest extends DuskTestCase
{
    use SetupDatabaseForTesting;
    use TestHasUsers;

    public function test_non_tooladmin_cant_view_ban_list()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->getUser())
                ->visit(route('admin.bans.list'))
                ->assertSee('403')
                ->assertDontSee('Add ban');
        });
    }

    public function test_tooladmin_can_view_ban_list()
    {
        Ban::factory()->count(3)->create();
        Ban::factory()->count(3)->setIP()->create();

        Ban::factory()->create(['target' => 'Visible ban', 'is_protected' => false, 'is_active' => true,]);
        Ban::factory()->create(['target' => 'Protected ban', 'is_protected' => true,]);

        Ban::factory()->create(['is_active' => false,]);

        Ban::factory()->create(['expiry' => Carbon::createFromTimestamp(0)->format('Y-m-d H:i:s')]);
        Ban::factory()->create(['expiry' => Carbon::create(2030, 01, 01, 10, 00, 00)->format('Y-m-d H:i:s')]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->getTooladminUser())
                ->visit(route('admin.bans.list'))
                ->assertDontSee('403')
                ->assertSee('Visible ban')
                ->assertDontSee('Protected ban')
                ->assertSee('ban target removed')
                ->assertSee('unbanned')
                ->assertSee('Add ban')
                ->assertSee('indefinite')
                ->assertSee('2030-01-01 10:00:00');
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
