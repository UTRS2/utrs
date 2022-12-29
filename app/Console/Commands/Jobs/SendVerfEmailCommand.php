<?php

namespace App\Console\Commands\Jobs;

use Illuminate\Console\Command;
use App\Services\Facades\MediaWikiRepository;
use App\Jobs\Scheduled\RemoveAppealPrivateDataJob;
use App\Models\Appeal;

class SendVerfEmailCommand extends Command
{
    protected $signature = 'utrs-jobs:verf-email {id}';
    protected $description = 'Send Verification email';

    public function handle()
    {
        if (!is_numeric($id)) {
          $this->error('Error: ID is not numeric');
          return 1;
        }
        $this->info('Sending Verf Email...');
        $appeal = Appeal::findOrFail($id);
        $url = url(route('public.appeal.verifyownership', [$appeal, $token]));
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
        $this->info('Done sending');
        return 0;
    }
}
