<?php

namespace App\Console\Commands;

use App\Permission;
use Illuminate\Console\Command;

class RemoveDuplicatePermissionsCommand extends Command
{
    protected $signature = 'utrs-maintenance:remove-duplicate-permissions 
                            {--dry? : A dry run of the command should be ran}';
    protected $description = 'Clears up legacy duplicate permissions from the database';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if (!isset($this->argument('--dry'))) {
            if (!$this->confirm('You are about to run a live database change. Are you sure? (Did you test it?)')) {
                return 1;
            }
        }
        $duplicates = Permission::with('user')
            ->selectRaw('wiki, userid, count(*) as count')
            ->groupBy(['wiki', 'userid'])
            ->having('count', '>', 1)
            ->get();

        $progressBar = $this->output->createProgressBar($duplicates->count());
        $progressBar->display();

        foreach ($duplicates as $duplicate) {
            if (!isset($this->argument('--dry'))) {
                /** @var Permission $duplicate */
                $this->info("Removing duplicates for user $duplicate->userid in wiki $duplicate->wiki...");

                // delete duplicates. can't use $duplicate->delete() due to it being a groupBy
                Permission::where('userid', $duplicate->userid)->where('wiki', $duplicate->wiki)->delete();

                // and load correct permissions from wiki
                $duplicate->user->queuePermissionChecks();
            }
            else {
                /** @var Permission $duplicate */
                $this->info("DRY: Wanting to removing duplicates for user $duplicate->userid in wiki $duplicate->wiki");
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        return 0;
    }
}
