<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Emails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // create email bans table
        // using the following structure
        // id - key, email - string, linkedappeals - array of ints, appealbanned - boolean, accountbanned - boolean, lastused - datetime, lastemail - datetime
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('uid');
            $table->string('linkedappeals')->nullable();
            $table->boolean('appealbanned')->default(false);
            $table->boolean('accountbanned')->default(false);
            $table->dateTime('lastused')->nullable();
            $table->dateTime('lastemail')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // drop email bans table
        Schema::dropIfExists('emails');
    }
}
