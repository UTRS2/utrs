<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignAppealIdOnPrivateData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('privatedatas', function (Blueprint $table) {
            $table->foreign('appeal_id')
                ->references('id')
                ->on('appeals')
                ->onDelete('cascade');
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
            $table->dropForeign(['appeal_id']);
        });
    }
}
