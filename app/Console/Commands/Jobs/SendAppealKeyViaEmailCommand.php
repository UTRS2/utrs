<?php

namespace App\Console\Commands\Jobs;

use Illuminate\Console\Command;
use App\Services\Facades\MediaWikiRepository;
use App\Jobs\Scheduled\RemoveAppealPrivateDataJob;
use App\Models\Appeal;
use Illuminate\Support\Str;

class SendAppealKeyViaEmailCommand extends Command
{
    protected $signature = 'utrs-jobs:sendappealkey {id}';
    protected $description = 'Send AppealKey to user via email';

    public function handle()
    {
        if (!is_numeric($this->argument('id'))) {
          $this->error('Error: ID is not numeric');
          return 1;
        }
        $this->info('Sending AppealKey Email...');
        $appeal = Appeal::findOrFail($this->argument('id'));
        // check if the user can be e-mailed according to MediaWiki API
        if ($appeal->user_verified == -1) {
            $this->info("User hasn't set email address onwiki - can't send key");
            return;
        }
        $title = 'Re: Copy of your AppealKey for your UTRS appeal';
         $message = <<<EOF
    Someone has requested to a UTRS Developer that your appeal key be send to you.
    
    Your Appeal Key: $appeal->appealsecretkey
    
    If this wasn't you, please inform a UTRS Developer so they can prevent further emails from coming to you.
    If this was you, please remeber to verify your account for your appeal if you haven't already.
    
    Thanks,
    UTRS Developers
    EOF;
        
        $result = MediaWikiRepository::getApiForTarget($appeal->wiki)->getMediaWikiExtras()->sendEmail($appeal->getWikiEmailUsername(), $title, $message);
        $this->info('Result: '.$result);
        $this->info('Done sending');
        return 0;
    }
}
