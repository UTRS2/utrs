<?php

use Illuminate\Database\Migrations\Migration;

class MigrateOldBlockedUserComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('logs')
            ->where('user', 0)
            ->whereNotNull('reason')
            ->where('action', 'comment')
            ->update([
                'action' => 'responded',
                'user' => -1,
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('logs')
            ->where('user', -1)
            ->whereNotNull('reason')
            ->where('action', 'responded')
            ->update([
                'action' => 'comment',
                'user' => 0,
            ]);
    }
}
