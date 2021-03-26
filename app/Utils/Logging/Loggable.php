<?php

namespace App\Utils\Logging;

use App\Models\LogEntry;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Loggable
{
    use HasRelationships;

    public function logs(): MorphMany
    {
        return $this->morphMany(LogEntry::class, 'model');
    }

    public function addLog(
        LogContext $context,
        string $action,
        ?string $reason = null,
        int $protection = LogEntry::LOG_PROTECTION_NONE
    ): void
    {
        $this->logs()->create([
            'user_id' => $context->getUserId(),
            'ip' => $context->getIpAddress(),
            'ua' => $context->getUserAgent(),
            'action' => $action,
            'reason' => $reason,
            'protected' => $protection,
        ]);
    }
}