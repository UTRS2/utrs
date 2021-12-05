<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePermissionsForNonexistentUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Clean up leftovers of any deleted users
        DB::table('permissions')
            ->whereNotIn(
                'userid',
                DB::table('users')
                    ->select('id')
                    ->distinct()
                    ->get()
            )
            ->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Nothing left here
    }
}
