<?php

use App\Log;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigrateOldBlockedUserComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Log::where('user', 0)
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
        Log::where('user', -1)
            ->whereNotNull('reason')
            ->where('action', 'responded')
            ->update([
                'action' => 'comment',
                'user' => 0,
            ]);
    }
}
