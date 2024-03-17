<?php

namespace Database\Seeders;

use App\Models\LogEntry;
use App\Models\Appeal;
use Illuminate\Database\Seeder;

class LogEntrySeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // get all the appeals
        $appeals = Appeal::all();

        //foreach appeal, create a log entry
        foreach ($appeals as $appeal) {
            LogEntry::factory()->create([
                'model_id' => $appeal->id,
                'model_type' => Appeal::class,
                'action' => 'create',
                'user_id' => -1,
                'timestamp' => $appeal->submitted,
                'protected' => 0,
            ]);
        }
    }
}
