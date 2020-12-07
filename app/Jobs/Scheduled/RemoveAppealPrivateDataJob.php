<?php

namespace App\Jobs\Scheduled;

use App\Models\Appeal;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Collection;

class RemoveAppealPrivateDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Find appeals that need to be purged.
     * @return Builder
     */
    public function fetchAppeals()
    {
        return Appeal::whereHas('privateData')
            ->whereIn('status', [
                Appeal::STATUS_ACCEPT,
                Appeal::STATUS_DECLINE,
                Appeal::STATUS_EXPIRE,
                Appeal::STATUS_INVALID,
            ])
            ->whereDoesntHave('comments', function (Builder $query) {
                $query->where('timestamp', '>=', now()->modify('-1 week'));
            });
    }


    /**
     * Purge one specified appeal.
     * @param Appeal $appeal
     */
    public function purge(Appeal $appeal)
    {
        $appeal->privateData->delete();
    }

    public function handle()
    {
        $this->fetchAppeals()
            ->chunkById(100, function (Collection $collection) {
                $collection->each(function (Appeal $appeal) {
                    $this->purge($appeal);
                });
            });
    }
}
