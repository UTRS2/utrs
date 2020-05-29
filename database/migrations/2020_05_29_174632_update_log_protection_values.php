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
            Log::where('protected', 1)
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
            Log::where('protected', 2)
                ->update([
                    'protected' => 1,
                ]);
        });
    }
}
