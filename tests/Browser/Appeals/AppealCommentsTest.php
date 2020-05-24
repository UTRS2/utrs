<?php

namespace Tests\Browser\Appeals;

use App\Appeal;
use App\Template;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Traits\TestHasUsers;
use Illuminate\Foundation\Testing\DatabaseMigrations;

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

    public function test_using_template()
    {
        $this->browse(function (Browser $browser) {
            $appeal = factory(Appeal::class)->create();

            factory(Template::class, 5)->create();
            $lastTemplate = factory(Template::class)->create();
            $lastTemplateStart = explode("\n", $lastTemplate->template)[0];

            $browser->loginAs($this->getUser())
                ->visit('/appeal/' . $appeal->id)
                ->assertSee(Appeal::STATUS_OPEN)
                ->assertDontSee(Appeal::STATUS_AWAITNG_REPLY)
                ->assertDontSee('Send a reply to user')
                ->assertDontSee($lastTemplateStart)
                ->clickLink('Reserve')
                ->clickLink('Send a reply to the user')
                ->assertSee('On this screen, you will see a list of templates to choose from in responding to a user')
                ->assertDontSee($lastTemplateStart)
                ->assertSee($lastTemplate->name)
                ->press($lastTemplate->name)
                ->assertSee($lastTemplateStart)
                ->select('#status-' . $lastTemplate->id, Appeal::STATUS_AWAITNG_REPLY)
                ->press('Submit')
                ->assertSee(Appeal::STATUS_AWAITNG_REPLY)
                ->assertDontSee(Appeal::STATUS_OPEN)
                ->assertSee($lastTemplateStart);

            $appeal->refresh();

            $this->assertEquals(Appeal::STATUS_AWAITNG_REPLY, $appeal->status);
        });
    }

    public function test_custom_reply()
    {
        $this->browse(function (Browser $browser) {
            $appeal = factory(Appeal::class)->create();

            $browser->loginAs($this->getUser())
                ->visit('/appeal/' . $appeal->id)
                ->assertSee(Appeal::STATUS_OPEN)
                ->assertDontSee('Send a reply to user')
                ->clickLink('Reserve')
                ->clickLink('Send a reply to the user')
                ->assertSee('On this screen, you will see a list of templates to choose from in responding to a user')
                ->clickLink('Reply custom text')
                ->type('custom', 'Go away.')
                ->press('Submit')
                ->assertSee(Appeal::STATUS_OPEN)
                ->assertDontSee('set status as ');

            $appeal->refresh();

            $this->assertEquals(Appeal::STATUS_OPEN, $appeal->status);
        });
    }
}
