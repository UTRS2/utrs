<?php

use App\Models\Wiki;
use Illuminate\Database\Migrations\Migration;
use App\Console\Commands\ImportWikisFromConfig;

class SyncInitialWikis extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Artisan::call(ImportWikisFromConfig::class, ['--force' => true]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Wiki::truncate();
    }
}
