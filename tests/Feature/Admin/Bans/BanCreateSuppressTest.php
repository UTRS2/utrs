<?php

namespace Tests\Feature\Admin\Bans;

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
                    'target' => '1.2.3.4/24',
                    'reason' => 'foo ar',
                    'expiry' => 'indefinite',
                    'wiki_id' => $wikiId,
                    'is_protected' => true
                ]
            )
            ->assertSessionHasErrors([ 'is_protected' ]);
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
}
