<?php

namespace App\Console\Commands\Jobs;

use Illuminate\Console\Command;
use App\Services\Facades\MediaWikiRepository;
use App\Jobs\Scheduled\RemoveAppealPrivateDataJob;

class RemoveAppealPrivateDataCommand extends Command
{
    protected $signature = 'utrs-jobs:remove-appeal-private-data {--wiki=all}';
    protected $description = 'Remove appeal private data';

    public function handle()
    {
        $wikis = $this->option('wiki');
        $wikis = $wikis === 'all'
            ? MediaWikiRepository::getSupportedTargets()
            : explode(',', $wikis);

        foreach ($wikis as $wiki) {
            $this->line("Scheduling for $wiki");
            RemoveAppealPrivateDataJob::dispatch($wiki);
        }

        return 0;
    }
}
