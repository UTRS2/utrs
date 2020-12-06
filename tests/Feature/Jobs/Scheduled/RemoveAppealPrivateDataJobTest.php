<?php

namespace Tests\Feature\Jobs\Scheduled;

use Tests\TestCase;
use App\Models\Appeal;
use App\Models\LogEntry;
use App\Models\Privatedata;
use Tests\Traits\TestHasUsers;
use App\Jobs\Scheduled\RemoveAppealPrivateDataJob;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class RemoveAppealPrivateDataJobTest extends TestCase
{
    use TestHasUsers;
    use DatabaseMigrations;

    private function isPurged(Appeal $appeal): bool
    {
        $job = new RemoveAppealPrivateDataJob($appeal->wiki);
        return $job->fetchAppeals()
            ->where('id', $appeal->id)
            ->exists();
    }

    public function test_should_not_purge_old_open_appeal()
    {
        $appeal = Appeal::factory()
            ->has(Privatedata::factory())
            ->create();

        LogEntry::create([
            'user_id' => $this->getUser()->id,
            'model_id' => $appeal->id,
            'model_type' => Appeal::class,
            'action' => 'comment',
            'reason' => 'foo bar',
            'ip' => '1.2.3.4',
            'ua' => 'DB/1',
            'protected' => LogEntry::LOG_PROTECTION_NONE,
            'timestamp' => now()->modify('-2 weeks'),
        ]);

        $this->assertFalse($this->isPurged($appeal));
    }

    public function test_should_not_purge_recently_closed_appeal()
    {
        $appeal = Appeal::factory()
            ->has(Privatedata::factory())
            ->create([
                'status' => Appeal::STATUS_DECLINE,
            ]);

        LogEntry::create([
            'user_id' => $this->getUser()->id,
            'model_id' => $appeal->id,
            'model_type' => Appeal::class,
            'action' => 'comment',
            'reason' => 'foo bar',
            'ip' => '1.2.3.4',
            'ua' => 'DB/1',
            'protected' => LogEntry::LOG_PROTECTION_NONE,
            'timestamp' => now()->modify('-1 day'),
        ]);

        $this->assertFalse($this->isPurged($appeal));
    }

    public function test_should_purge_old_closed_appeal()
    {
        $appeal = Appeal::factory()
            ->has(Privatedata::factory())
            ->create([
                'status' => Appeal::STATUS_DECLINE,
            ]);

        LogEntry::create([
            'user_id' => $this->getUser()->id,
            'model_id' => $appeal->id,
            'model_type' => Appeal::class,
            'action' => 'comment',
            'reason' => 'foo bar',
            'ip' => '1.2.3.4',
            'ua' => 'DB/1',
            'protected' => LogEntry::LOG_PROTECTION_NONE,
            'timestamp' => now()->modify('-1 month'),
        ]);

        $this->assertTrue($this->isPurged($appeal));
    }

    public function test_should_not_purge_old_closed_appeal_with_no_data()
    {
        $appeal = Appeal::factory()
            ->create([
                'status' => Appeal::STATUS_DECLINE,
            ]);

        LogEntry::create([
            'user_id' => $this->getUser()->id,
            'model_id' => $appeal->id,
            'model_type' => Appeal::class,
            'action' => 'comment',
            'reason' => 'foo bar',
            'ip' => '1.2.3.4',
            'ua' => 'DB/1',
            'protected' => LogEntry::LOG_PROTECTION_NONE,
            'timestamp' => now()->modify('-1 month'),
        ]);

        $this->assertFalse($this->isPurged($appeal));
    }

    public function test_it_purges_correctly()
    {
        $appeal = Appeal::factory()
            ->has(Privatedata::factory())
            ->create([
                'status' => Appeal::STATUS_DECLINE,
            ]);

        $job = new RemoveAppealPrivateDataJob($appeal->wiki);

        $this->assertNotNull($appeal->privatedata);
        $this->assertNotEmpty($appeal->privatedata->ipaddress);

        $job->purge($appeal);
        $appeal->refresh();

        $this->assertNull($appeal->privatedata);
    }
}
