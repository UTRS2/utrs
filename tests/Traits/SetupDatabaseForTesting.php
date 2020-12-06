<?php

namespace Tests\Traits;

use App\Models\Wiki;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Extend {@link RefreshDatabase}, but also create some necessary objects
 */
trait SetupDatabaseForTesting
{
    use RefreshDatabase {
        refreshTestDatabase as parentRefreshTestDatabase;
    }

    protected function refreshTestDatabase()
    {
        $this->parentRefreshTestDatabase();

        Wiki::factory()->create([
            'database_name' => 'enwiki',
            'display_name' => 'English Wikipedia',
        ]);

        Wiki::factory()->create([
            'database_name' => 'global',
            'display_name' => 'Global blocks/locks',
        ]);
    }
}
