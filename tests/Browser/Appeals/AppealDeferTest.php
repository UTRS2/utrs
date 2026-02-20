<?php

namespace Tests\Browser\Appeals;

use App\Models\Appeal;
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
            $appeal = Appeal::factory()->create();

            $browser->visit('/changelang/en')
                ->loginAs($this->getUser())
                ->visit('/appeal/' . $appeal->id)
                ->assertSee(__('appeals.status.OPEN'))
                ->assertDontSee(__('appeals.status.ADMIN'))
                ->waitForText('Tool admin',2)
                ->press('Tool admin')
                ->assertSee(__('appeals.status.ADMIN'))
                ->assertDontSee(__('appeals.details-status').': '.__('appeals.status.OPEN'));

            $appeal->refresh();
            $this->assertEquals(Appeal::STATUS_ADMIN, $appeal->status);
            $this->assertNotNull($appeal->comments()->where('action', 'sent for tool administrator review')->first());
        });
    }
}
