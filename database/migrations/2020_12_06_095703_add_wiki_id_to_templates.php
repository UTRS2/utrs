<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWikiIdToTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->unsignedBigInteger('wiki_id')
                ->nullable(true);

            $table->foreign('wiki_id')
                ->references('id')
                ->on('wikis')
                ->onDelete('cascade');
            $table->index(['wiki_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropForeign(['wiki_id']);
            $table->dropIndex(['wiki_id', 'active']);
            $table->dropColumn('wiki_id');
        });
    }
}
