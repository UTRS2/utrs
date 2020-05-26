<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeCommentsLonger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sendresponses', function (Blueprint $table) {
            $table->text('custom')->change();
        });

        Schema::table('logs', function (Blueprint $table) {
            $table->text('reason')->change();
        });

        Schema::table('templates', function (Blueprint $table) {
            $table->text('template')->change();
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
            $table->string('template')->change();
        });

        Schema::table('logs', function (Blueprint $table) {
            $table->string('reason')->change();
        });

        Schema::table('sendresponses', function (Blueprint $table) {
            $table->string('custom')->change();
        });
    }
}
