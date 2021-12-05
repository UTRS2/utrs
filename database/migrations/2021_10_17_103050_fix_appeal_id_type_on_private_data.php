<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixAppealIdTypeOnPrivateData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('privatedatas', function (Blueprint $table) {
            $table->unsignedBigInteger('appeal_id')->change();
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
            $table->bigInteger('appeal_id')->change();
        });
    }
}
