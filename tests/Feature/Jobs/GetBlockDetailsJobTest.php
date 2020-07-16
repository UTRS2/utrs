<?php

namespace Tests\Feature\Jobs;

use App\Appeal;
use App\Jobs\GetBlockDetailsJob;
use App\Jobs\VerifyBlockJob;
use App\Services\Acc\Api\AccIntegration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Queue;
use Tests\CreatesApplication;
use Tests\Fakes\Acc\FakeAccIntegration;
use Tests\TestCase;

class GetBlockDetailsJobTest extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    public function test_it_should_defer_needed_appeals_to_acc()
    {
        Queue::fake();

        $this->app->bind(
            AccIntegration::class,
            FakeAccIntegration::class,
        );

        $validAppeal = factory(Appeal::class)->create(['status' => Appeal::STATUS_VERIFY, 'appealfor' => 'ACC Disallow 1']);
        $validBlock = ['user' => $validAppeal->appealfor, 'by' => 'Blocking administrator', 'reason' => 'Block reason'];

        $canDeferAppeal = factory(Appeal::class)->create(['status' => Appeal::STATUS_VERIFY, 'appealfor' => 'ACC Allow 1']);
        $canDeferBlock = ['user' => $canDeferAppeal->appealfor, 'by' => 'Blocking administrator', 'reason' => 'Block reason'];

        $mustDeferAppeal = factory(Appeal::class)->create(['status' => Appeal::STATUS_VERIFY, 'appealfor' => 'ACC Force 1']);
        $mustDeferBlock = ['user' => $mustDeferAppeal->appealfor, 'by' => 'Blocking administrator', 'reason' => 'Block reason'];

        $this->assertNull($validAppeal->block_target);
        $this->assertNull($canDeferAppeal->block_target);
        $this->assertNull($mustDeferAppeal->block_target);

        (new GetBlockDetailsJob($mustDeferAppeal))->handleBlockData($mustDeferBlock);
        Queue::assertNothingPushed();

        (new GetBlockDetailsJob($validAppeal))->handleBlockData($validBlock);
        (new GetBlockDetailsJob($canDeferAppeal))->handleBlockData($canDeferBlock);
        Queue::assertPushed(VerifyBlockJob::class, 2);

        $this->assertEquals(Appeal::STATUS_OPEN, $validAppeal->status);
        $this->assertEquals(Appeal::STATUS_OPEN, $canDeferAppeal->status);
        $this->assertEquals(Appeal::STATUS_REFER_ACC, $mustDeferAppeal->status);

        $this->assertEquals($validAppeal->appealfor, $validAppeal->block_target);
        $this->assertEquals($canDeferAppeal->appealfor, $canDeferAppeal->block_target);
        $this->assertEquals($mustDeferAppeal->appealfor, $mustDeferAppeal->block_target);
    }
}
