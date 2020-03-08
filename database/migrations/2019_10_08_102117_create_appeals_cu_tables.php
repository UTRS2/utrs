<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppealsCuTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appeals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('appealfor');
            $table->tinyInteger('privacylevel');
            $table->boolean('privacyreview');
            $table->tinyInteger('blocktype');
            $table->string('status');
            $table->boolean('blockfound');
            $table->string('blockingadmin')->nullable();
            $table->string('blockreason')->nullable();
            $table->timestamp('submitted');
            $table->bigInteger('handlingadmin')->nullable();
            $table->string('appealsecretkey');
            $table->string('appealtext', 8000);
            $table->string('wiki');
        });
        Schema::create('privatedatas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('appealID');
            $table->string('ipaddress');
            $table->string('useragent');
            $table->string('language')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appeals');
        Schema::dropIfExists('privatedata');
    }
}
