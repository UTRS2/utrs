<?php

namespace App\Console\Commands;

use App\Permission;
use Illuminate\Console\Command;

class RemoveDuplicatePermissionsCommand extends Command
{
    protected $signature = 'utrs-maintenance:remove-duplicate-permissions 
                            {--dry : A dry run of the command should be ran}';
    protected $description = 'Clears up legacy duplicate permissions from the database';

    public function handle()
    {
        $dry = $this->option('dry');

        if (!$dry && !$this->confirm('You are about to run a live database change. Are you sure? (Did you test it?)')) {
            $this->warn('Operation aborted.');
            return 1;
        }

        $duplicates = Permission::with('user')
            ->selectRaw('wiki, userid, count(*) as count')
            ->groupBy(['wiki', 'userid'])
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->warn('No duplicates found.');
            return 1;
        }

        $progressBar = $this->output->createProgressBar($duplicates->count());
        $progressBar->start();

        foreach ($duplicates as $duplicate) {
            /** @var Permission $duplicate */

            if ($dry) {
                $this->info("DRY: Would remove $duplicates->count duplicates for user $duplicate->userid in wiki $duplicate->wiki");
            } else {
                $this->info("Removing $duplicates->count duplicates for user $duplicate->userid in wiki $duplicate->wiki...");

                // delete duplicates. can't use $duplicate->delete() due to it being a groupBy
                Permission::where('userid', $duplicate->userid)->where('wiki', $duplicate->wiki)->delete();

                // and load correct permissions from wiki
                $duplicate->userObject->queuePermissionChecks();
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        return 0;
    }
}
