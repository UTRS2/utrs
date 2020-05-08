<?php

namespace App\Jobs\WikiPermission;

use App\User;
use Illuminate\Bus\Queueable;
use App\MediawikiIntegration\WikiApiUrls;
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

    public function getWikiUrl()
    {
        return WikiApiUrls::getGlobalWikiUrl();
    }

    public function getWikiId()
    {
        return '*';
    }

    public function getPermissionsToCheck()
    {
        return [
            'user',
            'steward',
            'sysop',
            'checkuser',
            'oversight',
        ];
    }
}
