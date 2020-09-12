<?php

namespace Tests\Browser\Appeals;

use App\Appeal;
use App\Log;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Traits\TestHasUsers;

class AppealDeferTest extends DuskTestCase
{
    use DatabaseMigrations;
    use TestHasUsers;

    public function test_can_defer_to_tooladmin()
    {
        $this->browse(function (Browser $browser) {
            $appeal = factory(Appeal::class)->create();

            $browser->loginAs($this->getUser())
                ->visit('/appeal/' . $appeal->id)
                ->assertSee(Appeal::STATUS_OPEN)
                ->assertDontSee(Appeal::STATUS_ADMIN)
                ->press('Tool admin')
                ->assertSee(Appeal::STATUS_ADMIN)
                ->assertDontSee(Appeal::STATUS_OPEN);

            $appeal->refresh();
            $this->assertEquals(Appeal::STATUS_ADMIN, $appeal->status);
            $this->assertNotNull($appeal->comments()->where('action', 'sent for tool administrator review')->first());
        });
    }
}
