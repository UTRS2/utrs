<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

class ReverifyAllUsersCommand extends Command
{
    protected $signature = 'utrs-maintenance:reverify-user-permissions';
    protected $description = 'Command description';

    public function handle()
    {
        $users = User::cursor();

        ProgressBar::setFormatDefinition('custom', ' %current%/%max% [%bar%] %percent:3s%% -- %message%');

        $progressBar = $this->output->createProgressBar($users->count());
        $progressBar->setFormat('custom');
        $progressBar->start();

        foreach ($progressBar->iterate($users) as $user) {
            /** @var User $user */
            $progressBar->setMessage("Re-verifying user $user->username (ID $user->id)...");
            $progressBar->display();
            $user->queuePermissionChecks();
        }

        $progressBar->setMessage('Done');
        $progressBar->finish();
        return 0;
    }
}
