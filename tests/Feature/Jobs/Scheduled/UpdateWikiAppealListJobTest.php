<?php

namespace Tests\Feature\Jobs\Scheduled;

use App\Models\Appeal;
use App\Services\Facades\MediaWikiRepository;
use App\Jobs\Scheduled\UpdateWikiAppealListJob;
use Tests\TestCase;

class UpdateWikiAppealListJobTest extends TestCase
{
    public function getJob()
    {
        return new UpdateWikiAppealListJob(MediaWikiRepository::getSupportedTargets()[0]);
    }

    public function test_returns_correct_value_when_empty()
    {
        $this->assertEquals('No open UTRS appeals.', $this->getJob()->createContents(collect()));
    }

    public function test_renders_standard_appeal_correctly()
    {
        $job = $this->getJob();
        // add ID manually, since we're not saving it into the database so it doesn't have one itself
        $appeal = Appeal::factory()->make([ 'id' => 1 ]);

        $text = $job->createContents(collect([$appeal]));

        // sanity check
        $this->assertEquals(Appeal::STATUS_OPEN, $appeal->status);

        $this->assertStringContainsString('User talk:' . $appeal->appealfor, $text);
        $this->assertStringContainsString(url(route('appeal.view', $appeal)), $text);
        $this->assertStringContainsString(Appeal::STATUS_OPEN, $text);
    }

    public function test_renders_block_id_appeal_correctly()
    {
        $job = $this->getJob();
        // add ID manually, since we're not saving it into the database so it doesn't have one itself
        $appeal = Appeal::factory()->make([ 'id' => 1, 'appealfor' => '#1']);

        $text = $job->createContents(collect([$appeal]));

        // sanity check
        $this->assertEquals(Appeal::STATUS_OPEN, $appeal->status);

        $this->assertStringContainsString('[{{fullurl:Special:BlockList|wpTarget=' . urlencode($appeal->appealfor) . '}} Block ID ' . $appeal->appealfor . ']', $text);
        $this->assertStringContainsString(url(route('appeal.view', $appeal)), $text);
        $this->assertStringContainsString(Appeal::STATUS_OPEN, $text);
    }

}
