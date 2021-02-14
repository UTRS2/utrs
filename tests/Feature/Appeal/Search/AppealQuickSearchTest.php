<?php

namespace Appeal\Search;

use App\Models\Appeal;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestHasUsers;

/**
 * @covers \App\Http\Controllers\Appeal\AppealQuickSearchController
 */
class AppealQuickSearchTest extends TestCase
{
    use TestHasUsers;
    use DatabaseMigrations;

    public function testFindByUsername()
    {
        $appeal = Appeal::factory()->create();

        Appeal::factory()->create([
            'submitted' => now()->modify('-1 month'),
        ]);

        $response = $this
            ->actingAs($this->getUser())
            ->call('GET', route('appeal.search.quick'), ['search' => $appeal->appealfor]);

        $response->assertRedirect(route('appeal.view', $appeal));
    }

    public function testFindById()
    {
        $appeal = Appeal::factory()->create();

        $response = $this
            ->actingAs($this->getUser())
            ->call('GET', route('appeal.search.quick'), ['search' => '#' . $appeal->id]);

        $response->assertRedirect(route('appeal.view', $appeal));
    }

    public function testNotFound()
    {
        $appeal = Appeal::factory()->create();

        $response = $this
            ->actingAs($this->getUser())
            ->call('GET', route('appeal.search.quick'), ['search' => 'foo' . $appeal->appealfor]);

        $response->assertRedirect(route('appeal.list'));
        $response->assertSessionHasErrors(['search']);
    }

    public function testFindCantSee()
    {
        $appeal = Appeal::factory()->create(['wiki' => 'ptwiki']);

        $response = $this
            ->actingAs($this->getUser())
            ->call('GET', route('appeal.search.quick'), ['search' => '#' . $appeal->id]);

        $response->assertRedirect(route('appeal.list'));
        $response->assertSessionHasErrors(['search']);
    }
}
