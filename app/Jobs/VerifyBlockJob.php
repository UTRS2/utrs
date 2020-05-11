<?php

namespace App\Jobs;

use App\Log;
use App\Appeal;
use App\MwApi\MwApiExtras;
use Illuminate\Support\Str;
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
            Log::create([
                'user' => 0,
                'referenceobject' => $this->appeal->id,
                'objecttype' => 'appeal',
                'action' => 'account verification',
                'reason' => 'user can not be e-mailed thru wiki',
                'ip' => '127.0.0.1',
                'ua' => '',
                'protected' => 0,
            ]);

            return;
        }

        $token = Str::random(32);

        $this->appeal->update([
            'verify_token' => $token,
        ]);

        MwApiExtras::sendEmail($this->appeal->wiki, $this->appeal->getWikiEmailUsername(),
            'foo', $token);

        Log::create([
            'user' => 0,
            'referenceobject' => $this->appeal->id,
            'objecttype' => 'appeal',
            'action' => 'account verification',
            'reason' => 'user e-mailed thru wiki',
            'ip' => '127.0.0.1',
            'ua' => '',
            'protected' => 0,
        ]);
    }
}
