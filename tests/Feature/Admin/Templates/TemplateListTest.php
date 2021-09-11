<?php

namespace Tests\Feature\Admin\Templates;

use App\Models\Wiki;
use App\Models\Template;
use Tests\Traits\TestHasUsers;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class TemplateListTest extends TestCase
{
    use TestHasUsers;
    use DatabaseMigrations;

    public function test_tooladmin_can_view_template_list()
    {
        $user = $this->getTooladminUser();

        // sanity check
        $this->assertEquals(['enwiki'], $user->permissions->where('tooladmin', 1)->pluck('wiki')->all());

        $wikiWithTooladmin = Wiki::where('database_name', 'enwiki')->first();
        $wikiWithoutTooladmin = Wiki::where('database_name', '!=', 'enwiki')->first();

        $templateCanSee = Template::factory()->create([ 'wiki_id' => $wikiWithTooladmin->id, ]);
        $templateCantSee = Template::factory()->create([ 'wiki_id' => $wikiWithoutTooladmin->id, ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/templates');

        $response->assertSee($templateCanSee->name)
            ->assertDontSee($templateCantSee->name);
    }
}
