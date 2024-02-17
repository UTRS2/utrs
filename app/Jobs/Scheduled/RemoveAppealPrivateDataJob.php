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
    public function fetchAppeals($timeline = '-1 week')
    {
        return Appeal::whereHas('privateData')
            ->whereIn('status', [
                Appeal::STATUS_ACCEPT,
                Appeal::STATUS_DECLINE,
                Appeal::STATUS_EXPIRE,
                Appeal::STATUS_INVALID,
            ])
            ->whereDoesntHave('comments', function (Builder $query) {
                $query->where('timestamp', '>=', now()->modify($timeline));
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

    /**
     * Purge all email data from an appeal.
     * @param Appeal $appeal
     */
    public function purgeEmail(Appeal $appeal)
    {
        $appeal->email = null;
        $appeal->save();
        //remove the appealid from the emailban table
        $linkedappeals = EmailBan::where('linkedappeals', 'LIKE', $appeal->id)->first();
        // count the number of linked appeals split by the comma
        try {
            $linkedappeals = explode(',', $linkedappeals);
            // remove the appealid from the array
            $linkedappeals = array_diff($linkedappeals, [$appeal->id]);
            // join the array back into a string
            $linkedappeals = implode(',', $linkedappeals);
            // update the linkedappeals field in the emailban table
            $linkedappeals->linkedappeals = $linkedappeals;
            $linkedappeals->save();
        } catch (\Exception $e) {
            $linkedappeal = EmailBan::where('linkedappeals', $appeal->id)->first();
            $linkedappeal->linkedappeals = null;
            $linkedappeal->save();
        }
        
    }

    public function handle()
    {
        $this->fetchAppeals('-1 week')->chunkById(100, function (Collection $collection) {
            $collection->each(function (Appeal $appeal) {
                $this->purge($appeal);
            });
        });
        
        $this->fetchAppeals('-6 months')->chunkById(100, function (Collection $collection) {
            $collection->each(function (Appeal $appeal) {
                $this->purgeEmail($appeal);
            });
        });
    }
}
