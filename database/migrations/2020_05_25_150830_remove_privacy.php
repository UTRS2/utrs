<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePrivacy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('privacy');
        });

        Schema::table('appeals', function (Blueprint $table) {
            $table->dropColumn('privacyreview');
            $table->dropColumn('privacylevel');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('appeals', function (Blueprint $table) {
            $table->integer('privacylevel')->nullable();
            $table->boolean('privacyreview')->nullable();
        });
        Schema::table('permissions', function (Blueprint $table) {
            $table->boolean('privacy')->default(0);
        });
    }
}
