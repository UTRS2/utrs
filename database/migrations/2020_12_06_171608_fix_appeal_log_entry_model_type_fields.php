<?php

use App\Models\Appeal;
use Illuminate\Database\Migrations\Migration;

class FixAppealLogEntryModelTypeFields extends Migration
{
    public function up()
    {
        DB::table('log_entries')
            ->where('model_type', 'appeal')
            ->update([ 'model_type' => Appeal::class ]);
    }

    public function down()
    {
        // will be reversed in another migration
    }
}
