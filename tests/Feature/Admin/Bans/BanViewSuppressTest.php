<?php

namespace Tests\Feature\Admin\Bans;

use App\Models\Ban;
use App\Models\Wiki;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\TestHasUsers;

class BanViewSuppressTest extends TestCase
{
    use DatabaseMigrations;
    use WithFaker;
    use TestHasUsers;

    /**
     * @test
     * @dataProvider provideTooladmin
     * @param string $suppressedOn
     * @param array $hasOversightOn
     * @param bool $expectedResult
     */
    public function testBanTargetSuppression(string $suppressedOn, array $hasOversightOn, bool $expectedResult)
    {
        $rights = collect(['enwiki', 'ptwiki', 'global'])
            ->mapWithKeys(function (string $wiki) use ($hasOversightOn) {
                return [
                    $wiki => in_array($wiki, $hasOversightOn)
                        ? ['user', 'admin', 'tooladmin', 'oversight']
                        : ($wiki === 'global' ? [] : ['user', 'admin', 'tooladmin'])
                ];
            });

        $user = $this->getUser($rights);

        $wikiId = Wiki::where('database_name', $suppressedOn)
            ->firstOrFail()->id;

        $ban = Ban::factory()->create([
            'is_protected' => 1,
            'is_active' => 1,
            'wiki_id' => $wikiId,
            'target' => 'Definitely suppressed ban target',
        ]);

        $page = $this->actingAs($user)
            ->get(route('admin.bans.view', $ban))
            ->assertSee(__('admin.bans.all'));

        if ($expectedResult) {
            $page->assertSee('Definitely suppressed ban target');
        } else {
            $page->assertDontSee('Definitely suppressed ban target');
        }
    }

    public function provideTooladmin(): array
    {
        return [
            'Local oversighter can view suppressed' => [ 'enwiki', [ 'enwiki' ], true ],
            'Global oversighter can view suppressed' => [ 'enwiki', [ 'global' ], true ],
            'Wrong wiki oversighter can not view suppressed' => [ 'enwiki', [ 'ptwiki' ], false ],
            'Regular user can not view suppressed' => [ 'enwiki', [], false ],
        ];
    }
}