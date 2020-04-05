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
            $table->bigInteger('userid');
            $table->boolean('oversight')->default(0);
            $table->boolean('checkuser')->default(0);
            $table->boolean('steward')->default(0);
            $table->boolean('staff')->default(0);
            $table->boolean('developer')->default(0);
            $table->boolean('tooladmin')->default(0);
            $table->boolean('privacy')->default(0);
            $table->boolean('admin')->default(0);
            $table->boolean('user')->default(0);
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
