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
        // create a table with an appeal id, request name, and submitted date
        Schema::create('pythonjob', function (Blueprint $table) {
            $table->id();
            $table->string('appeal_id');
            $table->string('request_name');
            $table->string('status')->default('pending');
            $table->timestamp('submitted_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pythonjob');
    }
};
