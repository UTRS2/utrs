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
        // make a table that will store the translations based on the appeal, comment id and the language
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->integer('appeal_id')->constrained();
            $table->integer('log_entries_id')->constrained();
            $table->string('language');
            $table->text('translation');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // drop the translations table
        Schema::dropIfExists('translations');
    }
};
