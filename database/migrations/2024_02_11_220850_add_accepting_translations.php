<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add accepting_translations column to wikis table
        Schema::table('wikis', function (Blueprint $table) {
            $table->boolean('accepting_translations')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop accepting_translations column from wikis table
        Schema::table('wikis', function (Blueprint $table) {
            $table->dropColumn('accepting_translations');
        });
    }
};
