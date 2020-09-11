<?php

namespace App\Console\Commands\Jobs;

use App\Jobs\Scheduled\UpdateWikiAppealListJob;
use App\MwApi\MwApiUrls;
use Illuminate\Console\Command;

class UpdateAppealTableOnWiki extends Command
{
    protected $signature = 'utrs-jobs:update-appeal-tables {--wiki=all}';
    protected $description = 'Command description';

    public function handle()
    {
        $wikis = $this->option('wiki');
        $wikis = $wikis === 'all'
            ? MwApiUrls::getSupportedWikis()
            : explode(',', $wikis);

        foreach ($wikis as $wiki) {
            $this->line("Scheduling for $wiki");
            UpdateWikiAppealListJob::dispatch($wiki);
        }
    }
}
