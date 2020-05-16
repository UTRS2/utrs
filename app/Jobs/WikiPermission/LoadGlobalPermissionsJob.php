<?php

namespace App\Jobs\WikiPermission;

use App\User;
use App\MwApi\MwApiUrls;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LoadGlobalPermissionsJob extends BaseWikiPermissionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getWikiId()
    {
        return '*';
    }

    protected function getValueInAllowedWikis()
    {
        return 'global';
    }

    public function getPermissionsToCheck()
    {
        return [
            'user',
            'steward',
            'staff',
        ];
    }
}
