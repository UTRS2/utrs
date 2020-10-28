<?php

use App\Models\LogEntry;
use Illuminate\Database\Migrations\Migration;

class ChangeOldLogEntryModelTypes extends Migration
{
    /**
     * Mapping of values to change.
     * @var array
     */
    const MAP = [
        'appeal'   => 'App\Models\Appeal',
        'template' => 'App\Models\Template',
        'App\Ban'  => 'App\Models\Ban',
        'App\User' => 'App\Models\User',
        'ban'      => 'App\Models\Ban',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (self::MAP as $old => $new) {
            LogEntry::where('model_type', $old)
                ->update([ 'model_type' => $new ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach (self::MAP as $old => $new) {
            LogEntry::where('model_type', $new)
                ->update([ 'model_type' => $old ]);
        }
    }
}
