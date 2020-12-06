<?php

namespace Tests\Traits;

use App\Models\Wiki;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * Extend {@link RefreshDatabase}, but also create some necessary objects
 */
trait SetupDatabaseForTesting
{
    use DatabaseMigrations {
        runDatabaseMigrations as parentRunDatabaseMigrations;
    }

    protected function runDatabaseMigrations()
    {
        $this->parentRunDatabaseMigrations();

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
