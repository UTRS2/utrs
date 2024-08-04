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
        // Add the acc column to the appeals table and if an email is verified
        Schema::table('appeals', function (Blueprint $table) {
            $table->string('acc')->nullable();
            $table->boolean('email_confirmed')->default(false)->after('email');
        });

        // Make the ACC table
        Schema::create('accs', function (Blueprint $table) {
            $table->id();
            $table->string('token');
            $table->string('status');
            $table->bigInteger('appeal_id');
            $table->bigInteger('acc_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the acc column from the appeals table
        Schema::table('appeals', function (Blueprint $table) {
            $table->dropColumn('acc');
            $table->dropColumn('email_confirmed');
        });

        // Drop the ACC table
        Schema::dropIfExists('accs');
    }
};
