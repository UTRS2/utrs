<?php

namespace App\Jobs\Scheduled;

use App\Models\LogEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Database\Eloquent\Collection;

class RemoveLogEntryPrivateDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Find log entries that need to be purged.
     * @return Builder
     */
    public function fetchLogEntries()
    {
        return LogEntry::where('timestamp', '<', now()->modify('-2 weeks'))
                ->where(function (Builder $query) {
                    $query->whereNull('ip')
                        ->orWhereNull('ua');
                });
    }

    /**
     * Purge one given log entry.
     * @param LogEntry $logEntry
     */
    public function purge(LogEntry $logEntry)
    {
        $logEntry->update([
            'ip' => NULL,
            'ua' => NULL,
        ]);
    }

    public function handle()
    {
        $this->fetchLogEntries()
            ->chunkById(100, function (Collection $collection) {
                $collection->each(function (LogEntry $logEntry) {
                    $this->purge($logEntry);
                });
            });
    }
}
