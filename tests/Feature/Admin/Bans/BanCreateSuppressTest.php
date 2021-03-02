<?php

namespace Tests\Feature\Admin\Bans;

use App\Models\Ban;
use App\Models\Wiki;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\TestHasUsers;

class BanCreateSuppressTest extends TestCase
{
    use DatabaseMigrations;
    use WithFaker;
    use TestHasUsers;

    /**
     * @dataProvider provideTooladmin
     * @param array $wikis
     * @test
     */
    public function test_normal_tooladmin_cant_suppress(array $wikis)
    {
        $user = $this->getTooladminUser([], $wikis);

        // check that the dialog is not present
        $this->actingAs($user)
            ->get(route('admin.bans.new'))
            ->assertSee('Add ban')
            ->assertDontSee('Ban target visibility');

        // get an id of a wiki where the user is a tooladmin, or if globally, for enwiki
        $wikiId = Wiki::where('database_name', $wikis[0] === '*' ? 'enwiki' : $wikis)
            ->firstOrFail()
            ->id;

        $this->actingAs($user)
            ->post(
                route('admin.bans.create'),
                [
                    'target' => '192.0.2.15/32',
                    'reason' => 'foo ar',
                    'expiry' => 'indefinite',
                    'wiki_id' => $wikiId,
                    'is_protected' => true
                ]
            )
            ->assertSessionHasErrors([ 'is_protected' ]);

        $this->assertFalse(Ban::where('target', '192.0.2.15/32')->exists());
    }

    public function provideTooladmin(): array
    {
        return [
            'Normal wiki' => [ ['enwiki'], ],
            'Two normal wikis' => [ ['enwiki', 'ptwiki'], ],
            'Global queue only' => [ ['global'], ],
            'All wikis' => [ ['*'], ],
        ];
    }

    public function test_cant_suppress_wrong_wiki()
    {
        $user = $this->getUser([
            'enwiki' => [
                'user', 'admin', 'tooladmin',
            ],
            'ptwiki' => [
                'user', 'admin', 'tooladmin', 'oversight'
            ],
        ]);

        $this->actingAs($user)
            ->get(route('admin.bans.new'))
            ->assertSee('Add ban')
            ->assertSee('Ban target visibility');

        $enwikiId = Wiki::where('database_name', 'enwiki')
            ->firstOrFail()
            ->id;
        $ptwikiId = Wiki::where('database_name', 'ptwiki')
            ->firstOrFail()
            ->id;

        $this->actingAs($user)
            ->post(
                route('admin.bans.create'),
                [
                    'target' => '192.0.2.15/32',
                    'reason' => 'foo ar',
                    'expiry' => 'indefinite',
                    'wiki_id' => $enwikiId,
                    'is_protected' => true
                ]
            )
            ->assertSessionHasErrors([ 'is_protected' ]);

        $this->assertFalse(Ban::where('target', '192.0.2.15/32')->exists());

        $this->actingAs($user)
            ->post(
                route('admin.bans.create'),
                [
                    'target' => '192.0.2.16/32',
                    'reason' => 'foo ar',
                    'expiry' => 'indefinite',
                    'wiki_id' => $ptwikiId,
                    'is_protected' => true
                ]
            )
            ->assertRedirect();

        $this->assertTrue(Ban::where('target', '192.0.2.16/32')->exists());
    }
}
