<?php

namespace App\Console\Commands\Jobs;

use App\Models\Appeal;
use App\Models\LogEntry;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CloseExpiredNotFoundAppealsCommand extends Command
{
    protected $signature = 'utrs-jobs:close-expired-notfound';
    protected $description = 'Close old appeals with no ban found';

    /**
     * Find appeals that need to be closed.
     * @return Builder
     */
    public function fetchAppeals()
    {
        return Appeal::where('status', Appeal::STATUS_NOTFOUND)
            ->whereDoesntHave('comments', function (Builder $query) {
                $query->where('timestamp', '>=', now()->modify('-2 days'));
            });
    }


    /**
     * Close one specified appeal.
     * @param Appeal $appeal
     */
    public function close(Appeal $appeal)
    {
        $appeal->update([
            'status' => Appeal::STATUS_EXPIRE,
        ]);

        LogEntry::create([
            'user_id' => 0,
            'model_id' => $appeal->id,
            'model_type' => Appeal::class,
            'action' => 'closed - expired',
            'reason' => 'this appeal has no known block and has had no activity in the last two days',
            'ip' => 'DB entry',
            'ua' => 'DB/1',
            'protected' => LogEntry::LOG_PROTECTION_NONE,
        ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->fetchAppeals()
            ->chunkById(100, function (Collection $collection) {
                $this->info("Processing {$collection->count()} appeals...");

                $collection->each(function (Appeal $appeal) {
                    $this->close($appeal);
                });
            });

        $this->info('All done.');

        return 0;
    }
}