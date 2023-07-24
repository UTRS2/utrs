<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExpandUaAppeal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // change useragent column in privatedatas table to varchar(1000)
        Schema::table('privatedatas', function (Blueprint $table) {
            $table->string('useragent', 3000)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // change useragent column in privatedatas table to text
        Schema::table('privatedatas', function (Blueprint $table) {
            $table->text('useragent')->change();
        });
    }
}
