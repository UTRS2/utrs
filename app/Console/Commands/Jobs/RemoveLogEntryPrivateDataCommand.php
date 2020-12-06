<?php

namespace App\Console\Commands\Jobs;

use Illuminate\Console\Command;
use App\Jobs\Scheduled\RemoveAppealPrivateDataJob;
use App\Jobs\Scheduled\RemoveLogEntryPrivateDataJob;

class RemoveLogEntryPrivateDataCommand extends Command
{
    protected $signature = 'utrs-jobs:remove-log-entry-private-data {--wiki=all}';
    protected $description = 'Remove log entry private data';

    public function handle()
    {
        $this->info('Scheduling job');
        RemoveLogEntryPrivateDataJob::dispatch();

        return 0;
    }
}
