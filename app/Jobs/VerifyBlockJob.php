<?php

namespace App\Jobs;

use App\Log;
use App\Appeal;
use RuntimeException;
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

    public $tries = 3;

    private $appeal;

    public function __construct(Appeal $appeal)
    {
        $this->appeal = $appeal;
    }

    public function handle()
    {
        if (!MwApiExtras::canEmail($this->appeal->wiki, $this->appeal->getWikiEmailUsername())) {
            return;
        }

        $token = Str::random(32);

        $this->appeal->update([
            'verify_token' => $token,
        ]);

        $url = url(route('appeal.verifyownership', [$this->appeal, $token]));
        $title = 'UTRS appeal verification';
        $message = <<<EOF
Hello,

Someone appealed your Wikipedia block using the Unblock Ticket Request System (UTRS).
If this was you, please verify this appeal by using this link:

$url

If this wasn't you, no action is needed.

Thanks,
the UTRS team
EOF;


        $result = MwApiExtras::sendEmail($this->appeal->wiki, $this->appeal->getWikiEmailUsername(), $title, $message);

        if (!$result) {
            throw new RuntimeException('Failed sending an e-mail');
        }

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
