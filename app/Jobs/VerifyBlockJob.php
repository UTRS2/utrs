<?php

namespace App\Jobs;

use App\Models\Appeal;
use App\Services\Facades\MediaWikiRepository;
use Exception;
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
        // check if the user can be e-mailed according to MediaWiki API
        if (!MediaWikiRepository::getApiForTarget($this->appeal->wiki)->getMediaWikiExtras()->canEmail($this->appeal->getWikiEmailUsername())) {
            $this->appeal->update(['user_verified'=>-1]);
            return;
        }

        // create token
        $token = Str::random(32);
        if(env('APP_ENV')=="production") {
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
                $result = MediaWikiRepository::getApiForTarget($this->appeal->wiki)->getMediaWikiExtras()->sendEmail($this->appeal->getWikiEmailUsername(), $title, $message);

                if (!$result) {
                    throw new RuntimeException('Failed sending an e-mail: No result from MW API');
                }
            } catch (Exception $exception) {
                // wrap exception to add appeal number to log
                throw new RuntimeException('Failed to send verification email for appeal #' . $this->appeal->id . " - ".$exception->getMessage(), 0, $exception);
            }
        }

        // after the e-mail has been sent, persist the token in the database
        $this->appeal->update([
            'verify_token' => $token,
        ]);
    }

    public function displayName(): string
    {
        return get_class($this) . ': appeal #' . $this->appeal->id;
    }
}
