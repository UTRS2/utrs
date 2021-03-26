<?php

namespace Tests\Feature\Appeal;

use App\Models\Ban;
use App\Models\Wiki;
use App\Services\Facades\MediaWikiRepository;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AppealCreateBanTest extends TestCase
{
    use DatabaseMigrations;

    public function test_can_create_appeal_when_not_banned()
    {
        $wikiId = Wiki::where('database_name', MediaWikiRepository::getSupportedTargets()[0])
            ->firstOrFail()->id;

        Ban::factory()->create([
            'is_active' => true,
            'target' => 'Banned user 1',
            'reason' => 'Lorem ipsum text',
            'wiki_id' => $wikiId,
        ]);

        $response = $this->post('/public/appeal/store', [
            'test_do_not_actually_save_anything' => true,
            'appealtext' => 'Example appeal test',
            'appealfor' => 'Not banned user',
            'wiki' => MediaWikiRepository::getSupportedTargets()[0],
            'blocktype' => 1,
        ]);

        $response->assertStatus(200)
            ->assertSee('Test: not actually saving anything');
    }

    public function test_cant_create_appeal_when_account_is_banned()
    {
        $wikiId = Wiki::where('database_name', MediaWikiRepository::getSupportedTargets()[0])
            ->firstOrFail()->id;

        Ban::factory()->create([
            'is_active' => true,
            'target' => 'Banned user 1',
            'reason' => 'Lorem ipsum text',
            'wiki_id' => $wikiId,
        ]);

        $response = $this->post('/public/appeal/store', [
            'test_do_not_actually_save_anything' => true,
            'appealtext' => 'Example appeal test',
            'appealfor' => 'Banned user 1',
            'wiki' => MediaWikiRepository::getSupportedTargets()[0],
            'blocktype' => 1,
        ]);

        $response->assertStatus(403)
            ->assertSee('Lorem ipsum text');
    }

    public function test_cant_create_appeal_when_current_ip_is_banned()
    {
        $wikiId = Wiki::where('database_name', MediaWikiRepository::getSupportedTargets()[0])
            ->firstOrFail()->id;

        Ban::factory()->create([
            'is_active' => true,
            'target' => 'Banned user 1',
            'reason' => 'Lorem ipsum text',
            'wiki_id' => $wikiId,
        ]);

        Ban::factory()
            ->setIP()
            ->create([
                'is_active' => true,
                'target' => '127.0.0.1',
                'reason' => 'Foo bar text',
                'wiki_id' => $wikiId,
            ]);

        $response = $this->post('/public/appeal/store', [
            'test_do_not_actually_save_anything' => true,
            'appealtext' => 'Example appeal test',
            'appealfor' => 'Not banned user',
            'wiki' => MediaWikiRepository::getSupportedTargets()[0],
            'blocktype' => 1,
        ]);

        $response->assertStatus(403)
            ->assertSee('Foo bar text');
    }

    public function test_cant_create_appeal_when_current_ip_range_is_banned()
    {
        $wikiId = Wiki::where('database_name', MediaWikiRepository::getSupportedTargets()[0])
            ->firstOrFail()->id;

        Ban::factory()->create([
            'is_active' => true,
            'target' => 'Banned user 1',
            'reason' => 'Lorem ipsum text',
            'wiki_id' => $wikiId,
        ]);

        Ban::factory()
            ->setIP()
            ->create([
                'is_active' => true,
                'target' => '126.0.0.0/7',
                'reason' => 'Foo bar baz text',
                'wiki_id' => $wikiId,
            ]);

        $response = $this->post('/public/appeal/store', [
            'test_do_not_actually_save_anything' => true,
            'appealtext' => 'Example appeal test',
            'appealfor' => 'Not banned user',
            'wiki' => MediaWikiRepository::getSupportedTargets()[0],
            'blocktype' => 1,
        ]);

        $response->assertStatus(403)
            ->assertSee('Foo bar baz text');
    }

    public function test_cant_create_appeal_for_range_which_is_in_larger_ban()
    {
        $wikiId = Wiki::where('database_name', MediaWikiRepository::getSupportedTargets()[0])
            ->firstOrFail()->id;

        Ban::factory()
            ->setIP()
            ->create([
                'is_active' => true,
                'target' => '10.0.0.0/23',
                'reason' => 'Foo bar baz text',
                'wiki_id' => $wikiId,
            ]);

        $response = $this->post('/public/appeal/store', [
            'test_do_not_actually_save_anything' => true,
            'appealtext' => 'Example appeal test',
            'appealfor' => '10.0.0.0/24',
            'wiki' => MediaWikiRepository::getSupportedTargets()[0],
            'blocktype' => 0,
        ]);

        $response->assertStatus(403)
            ->assertSee('Foo bar baz text');
    }
}
