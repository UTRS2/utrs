<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameAppealIdOnPrivateData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('privatedatas', function (Blueprint $table) {
            $table->renameColumn('appealID', 'appeal_id');
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
            $table->renameColumn('appeal_id', 'appealID');
        });
    }
}
