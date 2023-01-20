<?php

namespace App\Console\Commands\Jobs;

use Illuminate\Console\Command;
use App\Services\Facades\MediaWikiRepository;
use App\Jobs\Scheduled\RemoveAppealPrivateDataJob;
use App\Models\Appeal;
use Illuminate\Support\Str;

class SendVerfEmailCommand extends Command
{
    protected $signature = 'utrs-jobs:verf-email {id}';
    protected $description = 'Send Verification email';

    public function handle()
    {
        if (!is_numeric($this->argument('id'))) {
          $this->error('Error: ID is not numeric');
          return 1;
        }
        $token = Str::random(32);
        $this->info('Sending Verf Email...');
        $appeal = Appeal::findOrFail($this->argument('id'));
        $url = url(route('public.appeal.verifyownership', [$appeal, $token]));
        // check if the user can be e-mailed according to MediaWiki API
        if (!MediaWikiRepository::getApiForTarget($appeal->wiki)->getMediaWikiExtras()->canEmail($appeal->getWikiEmailUsername())) {
            $this->info("User hasn't set email address onwiki");
            $appeal->user_verified=-1;
            $appeal->save();
            dd($appeal);
            return;
        }
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
        
        $result = MediaWikiRepository::getApiForTarget($appeal->wiki)->getMediaWikiExtras()->sendEmail($appeal->getWikiEmailUsername(), $title, $message);
        $this->info('Result: '.$result);
        $appeal->verify_token = $token;
        $appeal->save();
        $this->info('Done sending');
        return 0;
    }
}
