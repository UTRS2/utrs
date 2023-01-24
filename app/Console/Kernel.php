<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\Scheduled\PostGlobalIPBEReqJob;

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
        $schedule->command('utrs-jobs:remove-appeal-private-data')->hourlyAt(50);
        $schedule->command('utrs-jobs:remove-log-entry-private-data')->hourlyAt(50);

        // Wiki integration
        $schedule->command('utrs-jobs:update-appeal-tables --wiki=enwiki')->everyFifteenMinutes();
        $schedule->command('utrs-jobs:update-appeal-tables --wiki=global')->hourly();
        $schedule->job(new PostGlobalIPBEReqJob)->hourly();

        // Close expired NOTFOUND appeals
        $schedule->command('utrs-jobs:close-expired-notfound')->everyFourHours();

        // Permission updates
        $schedule->command('utrs-jobs:reverify-user-permissions')->daily()->at('14:00');
        
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
