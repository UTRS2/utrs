<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExpandUa extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('privatedatas', function (Blueprint $table) {
            $table->text('useragent')->change();
            $table->text('language')->change();
        });
        Schema::table('logs', function (Blueprint $table) {
            $table->text('ua')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('privatedatas', function (Blueprint $table) {
            $table->string('useragent')->change();
            $table->string('language')->change();
        });

        Schema::table('logs', function (Blueprint $table) {
            $table->string('ua')->change();
        });
    }
}
