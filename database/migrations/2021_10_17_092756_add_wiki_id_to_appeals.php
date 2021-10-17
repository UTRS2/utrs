<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWikiIdToAppeals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appeals', function (Blueprint $table) {
            $table->unsignedBigInteger('wiki_id')
                ->nullable(true);

            $table->foreign('wiki_id')
                ->references('id')
                ->on('wikis');
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
            $table->dropForeign(['wiki_id']);
            $table->dropColumn('wiki_id');
        });
    }
}
