<?php

namespace Tests\Feature\Appeal;

use App\Ban;
use App\MwApi\MwApiUrls;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AppealCreateBanTest extends TestCase
{
    use DatabaseMigrations;

    public function test_can_create_appeal_when_not_banned()
    {
        Ban::factory()->create(['is_active' => true, 'target' => 'Banned user 1', 'reason' => 'Lorem ipsum text']);

        $response = $this->post('/public/appeal/store', [
            'test_do_not_actually_save_anything' => true,
            'appealtext' => 'Example appeal test',
            'appealfor' => 'Not banned user',
            'wiki' => MwApiUrls::getSupportedWikis()[0],
            'blocktype' => 1,
        ]);

        $response->assertStatus(200)
            ->assertSee('Test: not actually saving anything');
    }

    public function test_cant_create_appeal_when_account_is_banned()
    {
        Ban::factory()->create(['is_active' => true, 'target' => 'Banned user 1', 'reason' => 'Lorem ipsum text']);

        $response = $this->post('/public/appeal/store', [
            'test_do_not_actually_save_anything' => true,
            'appealtext' => 'Example appeal test',
            'appealfor' => 'Banned user 1',
            'wiki' => MwApiUrls::getSupportedWikis()[0],
            'blocktype' => 1,
        ]);

        $response->assertStatus(403)
            ->assertSee('Lorem ipsum text');
    }

    public function test_cant_create_appeal_when_current_ip_is_banned()
    {
        Ban::factory()->create(['is_active' => true, 'target' => 'Banned user 1', 'reason' => 'Lorem ipsum text']);
        Ban::factory()->setIP()->create(['is_active' => true, 'target' => '127.0.0.1', 'reason' => 'Foo bar text']);

        $response = $this->post('/public/appeal/store', [
            'test_do_not_actually_save_anything' => true,
            'appealtext' => 'Example appeal test',
            'appealfor' => 'Not banned user',
            'wiki' => MwApiUrls::getSupportedWikis()[0],
            'blocktype' => 1,
        ]);

        $response->assertStatus(403)
            ->assertSee('Foo bar text');
    }

    public function test_cant_create_appeal_when_current_ip_range_is_banned()
    {
        Ban::factory()->create(['is_active' => true, 'target' => 'Banned user 1', 'reason' => 'Lorem ipsum text']);
        Ban::factory()->setIP()->create(['is_active' => true, 'target' => '126.0.0.0/7', 'reason' => 'Foo bar baz text']);

        $response = $this->post('/public/appeal/store', [
            'test_do_not_actually_save_anything' => true,
            'appealtext' => 'Example appeal test',
            'appealfor' => 'Not banned user',
            'wiki' => MwApiUrls::getSupportedWikis()[0],
            'blocktype' => 1,
        ]);

        $response->assertStatus(403)
            ->assertSee('Foo bar baz text');
    }

    public function test_cant_create_appeal_for_range_which_is_in_larger_ban()
    {
        Ban::factory()->setIP()->create(['is_active' => true, 'target' => '10.0.0.0/23', 'reason' => 'Foo bar baz text']);

        $response = $this->post('/public/appeal/store', [
            'test_do_not_actually_save_anything' => true,
            'appealtext' => 'Example appeal test',
            'appealfor' => '10.0.0.0/24',
            'wiki' => MwApiUrls::getSupportedWikis()[0],
            'blocktype' => 0,
        ]);

        $response->assertStatus(403)
            ->assertSee('Foo bar baz text');
    }
}
