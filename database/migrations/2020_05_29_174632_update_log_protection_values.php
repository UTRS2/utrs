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
        DB::transaction(function () {
            // do a round-robin swap:
            // first  1 => 3
            // second 2 => 1
            // third  3 => 2

            Log::where('protected', 1)
                ->update([
                    'protected' => 3,
                ]);
            Log::where('protected', 2)
                ->update([
                    'protected' => 1,
                ]);
            Log::where('protected', 3)
                ->update([
                    'protected' => 2,
                ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::transaction(function () {
            // do a round-robin swap:
            // first  1 => 3
            // second 2 => 1
            // third  3 => 2

            Log::where('protected', 1)
                ->update([
                    'protected' => 3,
                ]);
            Log::where('protected', 2)
                ->update([
                    'protected' => 1,
                ]);
            Log::where('protected', 3)
                ->update([
                    'protected' => 2,
                ]);
        });
    }
}
