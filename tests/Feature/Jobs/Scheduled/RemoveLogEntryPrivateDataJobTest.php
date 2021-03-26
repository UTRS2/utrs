<?php

namespace Tests\Feature\Jobs\Scheduled;

use App\Models\User;
use App\Models\Appeal;
use App\Models\LogEntry;
use Tests\Traits\TestHasUsers;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Jobs\Scheduled\RemoveAppealPrivateDataJob;
use App\Jobs\Scheduled\RemoveLogEntryPrivateDataJob;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class RemoveLogEntryPrivateDataJobTest extends TestCase
{
    use TestHasUsers;
    use DatabaseMigrations;

    public function test_should_not_purge_recent_log_entry()
    {
        $userId = $this->getUser()->id;
        $logEntry = LogEntry::create([
            'user_id' => $userId,
            'model_id' => $userId,
            'model_type' => User::class,
            'action' => 'comment',
            'reason' => 'foo bar',
            'ip' => '1.2.3.4',
            'ua' => 'DB/1',
            'protected' => LogEntry::LOG_PROTECTION_NONE,
            'timestamp' => now()->modify('-1 day'),
        ]);

        (new RemoveLogEntryPrivateDataJob())->handle();

        $logEntry->refresh();
        $this->assertEquals('1.2.3.4', $logEntry->ip);
    }

    public function test_should_purge_old_log_entry()
    {
        $userId = $this->getUser()->id;
        $logEntry = LogEntry::create([
            'user_id' => $userId,
            'model_id' => $userId,
            'model_type' => User::class,
            'action' => 'comment',
            'reason' => 'foo bar',
            'ip' => '1.2.3.4',
            'ua' => 'DB/1',
            'protected' => LogEntry::LOG_PROTECTION_NONE,
            'timestamp' => now()->modify('-1 year'),
        ]);

        (new RemoveLogEntryPrivateDataJob())->handle();

        $logEntry->refresh();
        $this->assertEmpty($logEntry->ip);
        $this->assertEmpty($logEntry->ua);
    }

    public function test_should_not_purge_already_purged_entries()
    {
        $userId = $this->getUser()->id;
        $logEntry = LogEntry::create([
            'user_id' => $userId,
            'model_id' => $userId,
            'model_type' => User::class,
            'action' => 'comment',
            'reason' => 'foo bar',
            'ip' => NULL,
            'ua' => NULL,
            'protected' => LogEntry::LOG_PROTECTION_NONE,
            'timestamp' => now()->modify('-1 year'),
        ]);

        $purged = (new RemoveLogEntryPrivateDataJob())
            ->fetchLogEntries()
            ->where('id', $logEntry->id)
            ->exists();

        $this->assertFalse($purged);
    }
}
