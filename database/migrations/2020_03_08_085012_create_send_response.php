<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSendResponse extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('sendresponses');
        Schema::create('sendresponses', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('appealID');
            $table->mediumInteger('template');
            $table->string('custom')->nullable();
            $table->boolean('sent')->default(0);
        });
        Schema::dropIfExists('template');
        Schema::dropIfExists('templates');
        Schema::create('templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('template');
            $table->boolean('active')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sendresponses');
        Schema::dropIfExists('template');
        Schema::dropIfExists('templates');
    }
}
