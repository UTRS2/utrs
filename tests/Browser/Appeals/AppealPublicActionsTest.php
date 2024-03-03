<?php

namespace Tests\Browser\Appeals;

use App\Models\Appeal;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AppealPublicActionsTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_user_can_see_own_appeal()
    {
        $this->browse(function (Browser $browser) {
            $appeal = Appeal::factory()->create();
            $appealTextStart = explode("\n", $appeal->appealtext)[0];

            $browser->visit('/changelang/en')
                    ->visit('/')->type('appealkey',$appeal->appealsecretkey)
                    ->waitForText('View my appeal',5)
                    ->clickLink('View my appeal')
                    ->assertSee('Appeal for "' . $appeal->appealfor . '"')
                    ->assertSee($appeal->status)
                    ->assertSee($appealTextStart)
                    ->assertSee('Add a comment to this appeal')
                    ->assertDontSee('We were not able to locate your block. Please');
        });
    }

    public function test_user_can_correct_own_appeal()
    {
        $this->browse(function (Browser $browser) {
            $appeal = Appeal::factory()->create([ 'status' => Appeal::STATUS_NOTFOUND, ]);

            $browser->visit('/changelang/en')
                ->visit('/')->type('appealkey',$appeal->appealsecretkey)
                ->waitForText('View my appeal',5)
                ->clickLink('View my appeal')
                ->assertSee('Appeal for "' . $appeal->appealfor . '"')
                ->assertSee(Appeal::STATUS_NOTFOUND)
                ->assertDontSee('Add a comment to this appeal')
                ->assertSee('We were not able to locate your block. Please')
                ->waitForText('Fix block information',5)
                ->clickLink('Fix block information')
                ->assertSee('You are now modifying your appeal to be resubmitted. Please ensure the information is correct.')
                ->assertInputValue('appealfor', $appeal->appealfor)
                ->type('appealfor', 'Blocked user')
                ->waitForText('Submit',5)
                ->clickLink('Submit')
                ->waitForText('View appeal details',5)
                ->clickLink('View appeal details')
                ->assertSee('Appeal for "Blocked user"'); 
        });
    }
}
