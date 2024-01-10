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
        // add three columns to the users table
        // one for the admin email but has to be unique, one for the admin email verified boolean, and one for the admin email verified token
        // also add two boolean columns: one for if the user wants to recieve notifications for appeal updates, the other for a weekly list of appeals that they are the blocking admin for
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->unique();
            $table->boolean('email_verified')->default(false);
            $table->string('email_verified_token')->nullable();
            $table->boolean('appeal_notifications')->default(false);
            $table->boolean('weekly_appeal_list')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // drop the three columns from the users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email');
            $table->dropColumn('email_verified');
            $table->dropColumn('email_verified_token');
            $table->dropColumn('appeal_notifications');
            $table->dropColumn('weekly_appeal_list');
        });
    }
};
