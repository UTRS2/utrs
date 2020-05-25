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
        if (Schema::hasColumn('permissions', 'privacy')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropColumn('privacy');
            });
        }

        if (Schema::hasColumn('appeals', 'privacyreview')) {
            Schema::table('appeals', function (Blueprint $table) {
                $table->dropColumn('privacyreview');
            });
        }

        if (Schema::hasColumn('appeals', 'privacylevel')) {
            Schema::table('appeals', function (Blueprint $table) {
                $table->dropColumn('privacylevel');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('appeals', function (Blueprint $table) {
            $table->integer('privacylevel')->nullable()->change();
            $table->boolean('privacyreview')->nullable()->change();
        });
        Schema::table('permissions', function (Blueprint $table) {
            $table->boolean('privacy')->default(0);
        });
    }
}
