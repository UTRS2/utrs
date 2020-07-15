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
        factory(Ban::class)->create(['is_active' => true, 'target' => 'Banned user 1', 'reason' => 'Lorem ipsum text']);

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
        factory(Ban::class)->create(['is_active' => true, 'target' => 'Banned user 1', 'reason' => 'Lorem ipsum text']);

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
        factory(Ban::class)->create(['is_active' => true, 'target' => 'Banned user 1', 'reason' => 'Lorem ipsum text']);
        factory(Ban::class, 'ip')->create(['is_active' => true, 'target' => '127.0.0.1', 'reason' => 'Foo bar text']);

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
        factory(Ban::class)->create(['is_active' => true, 'target' => 'Banned user 1', 'reason' => 'Lorem ipsum text']);
        factory(Ban::class, 'ip')->create(['is_active' => true, 'target' => '126.0.0.0/7', 'reason' => 'Foo bar baz text']);

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
}
