<?php

namespace App\Jobs;

use App\Models\Appeal;
use Exception;
use RuntimeException;
use App\MwApi\MwApiExtras;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

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
        // check if the user can be e-mailed according to MediaWiki API
        if (!MwApiExtras::canEmail($this->appeal->wiki, $this->appeal->getWikiEmailUsername())) {
            return;
        }

        // create token
        $token = Str::random(32);

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


        try {
            MwApiExtras::sendEmail($this->appeal->wiki, $this->appeal->getWikiEmailUsername(), $title, $message);
        } catch (Exception $exception) {
            // wrap exception to add appeal number to log
            throw new RuntimeException('Failed to send verification email for appeal #' . $this->appeal->id, 0, $exception);
        }

        // after the e-mail has been sent, persist the token in the database
        $this->appeal->update([
            'verify_token' => $token,
        ]);
    }
}
