<?php

namespace Tests\Feature\Admin\Bans;

use App\Models\Ban;
use App\Models\Wiki;
use App\Services\Facades\MediaWikiRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestHasUsers;

class BanListTest extends TestCase
{
    use DatabaseMigrations;
    use TestHasUsers;

    public function test_non_tooladmin_cant_view_ban_list()
    {
        $this->actingAs($this->getUser())
            ->get(route('admin.bans.list'))
            ->assertSee('403')
            ->assertDontSee('Add ban');
    }

    public function test_tooladmin_can_view_ban_list()
    {
        $wikiId = Wiki::where('database_name', MediaWikiRepository::getSupportedTargets()[0])
            ->firstOrFail()->id;

        Ban::factory()->count(3)->create([ 'wiki_id' => $wikiId, ]);
        Ban::factory()->count(3)->setIP()->create([ 'wiki_id' => $wikiId, ]);

        Ban::factory()->create([
            'target' => 'Visible ban',
            'is_protected' => false,
            'is_active' => true,
            'wiki_id' => $wikiId,
        ]);

        Ban::factory()->create([
            'target' => 'Protected ban',
            'is_protected' => true,
            'wiki_id' => $wikiId,
        ]);

        Ban::factory()->create([
            'is_active' => false,
            'wiki_id' => $wikiId,
        ]);

        Ban::factory()->create([
            'expiry' => Carbon::createFromTimestamp(0)->format('Y-m-d H:i:s'),
            'wiki_id' => $wikiId
        ]);

        Ban::factory()->create([
            'expiry' => Carbon::create(2030, 01, 01, 10, 00, 00)->format('Y-m-d H:i:s'),
            'wiki_id' => $wikiId
        ]);

        Ban::factory()->create([
            'target' => 'Ban on another wiki',
            'wiki_id' => $wikiId + 1,
        ]);

        Ban::factory()->create([
            'target' => 'Ban affecting all wikis',
            'is_protected' => false,
            'is_active' => true,
            'wiki_id' => null,
        ]);

        $this->actingAs($this->getTooladminUser())
            ->get(route('admin.bans.list'))
            ->assertDontSee('403')
            ->assertSee('Visible ban')
            ->assertDontSee('Protected ban')
            ->assertSee('No permission to view target')
            ->assertSee('Disabled')
            ->assertSee('New Ban')
            ->assertSee('Indefinite')
            ->assertSee('2030-01-01 10:00:00')
            ->assertDontSee('Ban on another wiki')
            ->assertDontSee('Ban affecting all wikis');
    }

    public function test_oversighter_can_view_oversighted_bans()
    {
        $wikiId = Wiki::where('database_name', MediaWikiRepository::getSupportedTargets()[0])
            ->firstOrFail()->id;

        Ban::factory()->create([
            'target' => 'Protected ban',
            'is_protected' => true,
            'wiki_id' => $wikiId,
        ]);

        $this->actingAs($this->getFunctionaryTooladminUser())
            ->get(route('admin.bans.list'))
            ->assertSee('Protected ban');
    }

    public function test_global_tooladmin_can_see_bans_affecting_all_wikis()
    {
        Ban::factory()->create([
            'target' => 'Ban affecting all wikis',
            'is_protected' => false,
            'is_active' => true,
            'wiki_id' => null,
        ]);

        $this->actingAs($this->getTooladminUser([], ['enwiki', 'global']))
            ->get(route('admin.bans.list'))
            ->assertSee('Ban affecting all wikis');
    }
}
