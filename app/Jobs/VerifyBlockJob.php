<?php

namespace App\Jobs;

use App\Models\Appeal;
use App\MwApi\MwApiExtras;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use RuntimeException;

class VerifyBlockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    private $appeal;

    /**
     * Construct the VerifyBlock job
     * @param Appeal $appeal - the Appeal object of the current appeal
     */
    public function __construct(Appeal $appeal)
    {
        $this->appeal = $appeal;
    }

    /**
     * Verify the person behind the appeal is actually the user.
     * @return void|RuntimeException
     */
    public function handle()
    {
        if (!MwApiExtras::canEmail($this->appeal->wiki, $this->appeal->getWikiEmailUsername())) {
            return;
        }

        $token = Str::random(32);

        $this->appeal->update([
            'verify_token' => $token,
        ]);

        $url = url(route('public.appeal.verifyownership', [$this->appeal, $token]));
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
    }
}
