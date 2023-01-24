<?php

namespace App\Console\Commands\Jobs;

use App\Services\Facades\MediaWikiRepository;
use App\Jobs\Scheduled\UpdateWikiAppealListJob;
use Illuminate\Console\Command;

class UpdateAppealTableOnWikiCommand extends Command
{
    protected $signature = 'utrs-jobs:update-appeal-tables {--wiki=all}';
    protected $description = 'Update on-wiki appeal tables';

    public function handle()
    {
        $wikis = $this->option('wiki');
        $wikis = $wikis === 'all'
            ? MediaWikiRepository::getSupportedTargets()
            : explode(',', $wikis);

        foreach ($wikis as $wiki) {
            $this->line("Scheduling for $wiki");
            UpdateWikiAppealListJob::dispatchNow($wiki);
        }
    }
}
