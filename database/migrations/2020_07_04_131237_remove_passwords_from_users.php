<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePasswordsFromUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        User::where('verified', false)->delete();

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password');
            $table->dropColumn('u_v_token');
            $table->dropColumn('verified');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('u_v_token')->nullable();
            $table->string('password')->default('');
            $table->boolean('verified')->default(0);
        });
    }
}
