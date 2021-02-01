<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropSendresponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('sendresponses');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('sendresponses', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('appealID');
            $table->mediumInteger('template');
            $table->text('custom')->nullable();
            $table->boolean('sent')->default(0);
        });
    }
}
