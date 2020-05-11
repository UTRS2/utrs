<?php

namespace App\Jobs;

use App\Appeal;
use App\MwApi\MwApiExtras;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VerifyBlockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $appeal;

    public function __construct(Appeal $appeal)
    {
        $this->appeal = $appeal;
    }

    public function handle()
    {
        if (!MwApiExtras::canEmail($this->appeal->wiki, $this->appeal->getWikiEmailUsername())) {
            // todo: do something?
            return;
        }

        MwApiExtras::sendEmail($this->appeal->wiki, $this->appeal->getWikiEmailUsername(), 'foo', 'bar');
    }
}
