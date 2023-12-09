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
        //add proxy flag to the appeals table
        Schema::table('appeals', function (Blueprint $table) {
            $table->boolean('proxy')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //remove the proxy flag from the appeals table
        Schema::table('appeals', function (Blueprint $table) {
            $table->dropColumn('proxy');
        });
    }
};
