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
        // add a column to the permissions table to store the api admins, after the developer column
        Schema::table('permissions', function (Blueprint $table) {
            $table->boolean('apiadmin')->nullable()->after('developer');
        });

        // add a new table to store the api keys
        // ensure that the api key is unique, and that the key has an expiration date and a boolean to determine if it is active
        // also add a key name, and a string that represents the permission level
        Schema::create('apikeys', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('permission');
            $table->dateTime('expires_at');
            $table->boolean('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // drop the column
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('apiadmin');
        });

        // drop the table
        Schema::dropIfExists('apikeys');
    }
};
