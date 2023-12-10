<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStewClerks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // add a 'stew_clerk' column to the 'permissions' table
        Schema::table('permissions', function (Blueprint $table) {
            $table->boolean('stew_clerk')->default(false)->after('steward');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //remove the 'stew_clerk' column from the 'permissions' table
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('stew_clerk');
        });
    }
}
