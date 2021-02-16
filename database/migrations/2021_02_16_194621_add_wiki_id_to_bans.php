<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWikiIdToBans extends Migration
{
    public function up()
    {
        Schema::table('bans', function (Blueprint $table) {
            $table->unsignedBigInteger('wiki_id')
                ->nullable(true);

            $table->foreign('wiki_id')
                ->references('id')
                ->on('wikis')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('bans', function (Blueprint $table) {
            $table->dropForeign(['wiki_id']);
            $table->dropColumn('wiki_id');
        });
    }
}