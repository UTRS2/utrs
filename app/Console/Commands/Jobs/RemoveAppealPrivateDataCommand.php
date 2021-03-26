<?php

namespace App\Console\Commands\Jobs;

use Illuminate\Console\Command;
use App\Services\Facades\MediaWikiRepository;
use App\Jobs\Scheduled\RemoveAppealPrivateDataJob;

class RemoveAppealPrivateDataCommand extends Command
{
    protected $signature = 'utrs-jobs:remove-appeal-private-data';
    protected $description = 'Remove appeal private data';

    public function handle()
    {
        $this->info('Scheduling job');
        RemoveAppealPrivateDataJob::dispatch();

        return 0;
    }
}
