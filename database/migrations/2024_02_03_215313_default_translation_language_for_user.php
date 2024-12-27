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
        // modify the users table to add a default translation language
        Schema::table('users', function (Blueprint $table) {
            $table->string('default_translation_language')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // drop the default translation language column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('default_translation_language');
        });
    }
};
