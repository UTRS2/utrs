<?php

namespace App\Console\Commands\Jobs;

use App\Models\Appeal;
use App\Models\LogEntry;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Jobs\GetBlockDetailsJob;

class CleanupBlockVerificationCommand extends Command
{
    protected $signature = 'utrs-jobs:run-verification';
    protected $description = 'Go through appeals needing verification';

    /**
     * Find appeals that need to be closed.
     * @return Builder
     */
    public function fetchAppeals()
    {
        return Appeal::where('status', Appeal::STATUS_VERIFY);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->fetchAppeals()
            ->chunkById(100, function (Collection $collection) {
                $this->info("Processing {$collection->count()} appeals...");

                $collection->each(function (Appeal $appeal) {
                    GetBlockDetailsJob::dispatchSync($appeal);
                });
            });

        $this->info('All done.');

        return 0;
    }
}