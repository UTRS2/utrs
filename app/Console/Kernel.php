<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Backups
        $schedule->command('backup:run --only-db')->daily()->at('23:00');
        $schedule->command('backup:clean')->daily()->at('01:00');
        $schedule->command('backup:monitor')->daily()->at('05:00');

        // Private data removal
        $schedule->command('utrs-jobs:remove-appeal-private-data')->daily()->at('09:00');
        $schedule->command('utrs-jobs:remove-log-entry-private-data')->daily()->at('10:00');

        // Wiki integration
        $schedule->command('utrs-jobs:update-appeal-tables --wiki=enwiki')->everyFifteenMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
