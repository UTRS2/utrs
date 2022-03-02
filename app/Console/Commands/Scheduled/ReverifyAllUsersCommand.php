<?php

namespace App\Console\Commands\Scheduled;

use App\Models\User;
use Illuminate\Console\Command;

class ReverifyAllUsersCommand extends Command
{
    protected $signature = 'utrs-jobs:reverify-user-permissions';
    protected $description = 'Re-verifies permissions of users that have not been verified recently';

    /** @var int All users should be re-verified every this many days. */
    protected $targetReverifyInterval = 14;

    public function handle()
    {
        $limit = (int) ceil(User::count() / $this->targetReverifyInterval) + 1;

        $this->info("Finding max $limit users");
        $users = User::limit($limit)
            ->orderBy('last_permission_check_at')
            ->cursor();

        foreach ($users as $user) {
            /** @var User $user */
            $this->info("Re-verifying user $user->username (ID $user->id)...");
            $user->queuePermissionChecks();
        }

        $this->info('Done.');
        return 0;
    }
}
