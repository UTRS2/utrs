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
        Schema::table('wikis', function (Blueprint $table) {
            // Add the default language column
            $table->string('default_language')->default('en-us')->after('display_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wikis', function (Blueprint $table) {
            // Drop the default language column
            $table->dropColumn('default_language');
        });
    }
};
