<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('oversight');
            $table->boolean('checkuser');
            $table->boolean('steward');
            $table->boolean('staff');
            $table->boolean('developer');
            $table->boolean('tooladmin');
            $table->boolean('privacy');
            $table->boolean('admin');
            $table->boolean('user');
            $table->string('wiki');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permissions');
    }
}
