<?php

namespace Tests\Browser\Admin\Bans;

use App\Ban;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Traits\TestHasUsers;

class BanViewTest extends DuskTestCase
{
    use DatabaseMigrations;
    use TestHasUsers;

    public function test_non_oversighter_cant_view_oversighted_bans()
    {
        factory(Ban::class)->create(['target' => 'Protected ban', 'is_protected' => true,]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->getTooladminUser())
                ->visit(route('admin.bans.list'))
                ->assertSee('ban target removed')
                ->assertDontSee('Protected ban');
        });
    }

    public function test_oversighter_can_view_oversighted_bans()
    {
        factory(Ban::class)->create(['target' => 'Protected ban', 'is_protected' => true,]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->getFunctionaryTooladminUser())
                ->visit(route('admin.bans.list'))
                ->assertSee('Protected ban');
        });
    }
}
