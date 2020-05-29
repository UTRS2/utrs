<?php

use App\Log;
use Illuminate\Database\Migrations\Migration;

class UpdateLogProtectionValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Log::where('protected', 1)
            ->update([
                'protected' => 2,
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Log::where('protected', 2)
            ->update([
                'protected' => 1,
            ]);
    }
}
