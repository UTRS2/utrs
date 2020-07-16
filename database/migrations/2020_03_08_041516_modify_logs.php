<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('logs', function(Blueprint $table) {
            $table->string('reason')->nullable()->change();
            $table->dropColumn('xff');
            $table->boolean('protected')->default(0);
            $table->timestamp('timestamp')->default(DB::raw('CURRENT_TIMESTAMP'));
            
        });
        Schema::table('logs', function(Blueprint $table) {
            $table->dropColumn('objecttype');
            $table->string('objecttype')->after('referenceobject');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('logs', function(Blueprint $table) {
            $table->string('xff');
            $table->string('reason')->change();
            $table->dropColumn('objecttype');
        });
        Schema::table('logs', function(Blueprint $table) {
            $table->dropColumn('protected');
            $table->bigInteger('objecttype')->nullable;
        });
        Schema::table('logs', function(Blueprint $table) {
            $table->dropColumn('timestamp');
        });
    }
}
