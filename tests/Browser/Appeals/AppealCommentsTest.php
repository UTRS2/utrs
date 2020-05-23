<?php

namespace Tests\Browser\Appeals;

use App\Appeal;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Traits\TestHasUsers;

class AppealCommentsTest extends DuskTestCase
{
    use DatabaseMigrations;
    use TestHasUsers;

    public function test_replying_to_marked_as_awaiting_reply()
    {
        $this->browse(function (Browser $browser) {
            $appeal = factory(Appeal::class)->create([
                'status' => Appeal::STATUS_AWAITNG_REPLY,
            ]);

            $browser->visit('/publicappeal?hash=' . $appeal->appealsecretkey)
                    ->assertSee(Appeal::STATUS_AWAITNG_REPLY)
                    ->type('comment', 'This is an example comment')
                    ->press('Submit')
                    ->assertSee('This is an example comment')
                    ->assertSee(Appeal::STATUS_OPEN)
                    ->assertDontSee(Appeal::STATUS_AWAITNG_REPLY);

            $appeal->refresh();

            $this->assertEquals(Appeal::STATUS_OPEN, $appeal->status);
        });
    }
}
