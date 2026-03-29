<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE log_entries MODIFY reason TEXT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // avoid truncation on revert: keep a large nullable type (safer for tests)
        DB::statement("ALTER TABLE log_entries MODIFY reason TEXT NULL");
    }
};
