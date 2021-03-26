<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeLogsPdFieldsNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('log_entries', function (Blueprint $table) {
            $table->string('ip',400)->nullable()->change();
            $table->text('ua')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_entries', function (Blueprint $table) {
            $table->string('ip')->change();
            $table->text('ua')->change();
        });
    }
}
