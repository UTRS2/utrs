<?php

namespace Tests\Browser\Appeals;

use App\Models\Appeal;
use App\Models\Template;
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
        $appeal = Appeal::factory()->create([
            'status' => Appeal::STATUS_AWAITING_REPLY,
        ]);

        $this->browse(function (Browser $browser) use ($appeal) {
            $browser->visit('/changelang/en')
                ->visit('/')->type('appealkey',$appeal->appealsecretkey)
                ->press('View my appeal')
                ->press(__('appeals.map.reviewappeal'))
                ->assertSee(Appeal::STATUS_AWAITING_REPLY)
                ->type('comment', 'This is an example comment')
                ->press(__('generic.submit'))
                ->press('View appeal details')
                ->assertSee('This is an example comment')
                ->assertSee(Appeal::STATUS_OPEN)
                ->assertDontSee(Appeal::STATUS_AWAITING_REPLY);
        });

        $appeal->refresh();

        $this->assertEquals(Appeal::STATUS_OPEN, $appeal->status);
    }

    public function test_using_template()
    {
        $appeal = Appeal::factory()->create();

        Template::factory()->count(2)->create([ 'active' => true, ]);

        $targetTemplate = Template::factory()->create([ 'active' => true, ]);
        $targetTemplateTextStart = explode("\n", $targetTemplate->template)[0];

        Template::factory()->count(2)->create([ 'active' => true, ]);

        $nonActiveTemplate = Template::factory()->create([ 'active' => false, ]);

        $this->browse(function (Browser $browser) use ($appeal, $targetTemplate, $targetTemplateTextStart, $nonActiveTemplate) {

            $browser->loginAs($this->getUser())
                ->visit('/changelang/en')
                ->visit('/appeal/' . $appeal->id)
                ->assertSee(__('appeals.status.OPEN'))
                ->assertDontSee(__('appeals.status.AWAITING_REPLY'))
                ->assertDontSee('Send a reply to user')
                ->assertDontSee($targetTemplateTextStart)
                ->waitForText('Reserve',2)
                ->press('Reserve')
                ->waitForText('Send a reply to the user',2)
                ->visit('/appeal/template/'.$appeal->id)
                ->assertSee(__('appeals.templates.alert'))
                ->assertDontSee($targetTemplateTextStart)
                ->assertSee($targetTemplate->name)
                ->assertDontSee($nonActiveTemplate->name)
                ->waitForText($targetTemplate->name,2)
                ->press($targetTemplate->name)
                ->waitForText($targetTemplateTextStart,2)
                ->assertSee($targetTemplateTextStart)
                ->select('#status-' . $targetTemplate->id, Appeal::STATUS_AWAITING_REPLY)
                ->waitForText(__('generic.submit'),2)
                ->press(__('generic.submit'))
                ->assertSee(__('appeals.status.AWAITING_REPLY'))
                ->assertDontSee(__('appeals.details-status').': '.__('appeals.status.OPEN'))
                ->assertSee($targetTemplateTextStart);
        });

        $appeal->refresh();

        $this->assertEquals(Appeal::STATUS_AWAITING_REPLY, $appeal->status);
    }

    public function test_custom_reply()
    {
        $appeal = Appeal::factory()->create();

        $this->browse(function (Browser $browser) use ($appeal) {
            $browser->loginAs($this->getUser())
                ->visit('/changelang/en')
                ->visit('/appeal/' . $appeal->id)
                ->assertSee(__('appeals.status.OPEN'))
                ->assertDontSee(__('appeals.send-reply-button'))
                ->waitForText('Reserve',2)
                ->press('Reserve')
                ->waitForText(__('appeals.send-reply-button'),2)
                ->press(__('appeals.send-reply-button'))
                //error above
                ->waitForText(__('appeals.templates.alert'),2)
                ->assertSee(__('appeals.templates.alert'))
                ->press(__('appeals.template.reply-custom'))
                ->type('custom', 'Go away.')
                ->waitForText(__('generic.submit'),2)
                ->press(__('generic.submit'))
                ->assertSee(__('appeals.status.OPEN'))
                ->assertDontSee('set status as ');
        });

        $appeal->refresh();

        $this->assertEquals(Appeal::STATUS_OPEN, $appeal->status);
    }
}
