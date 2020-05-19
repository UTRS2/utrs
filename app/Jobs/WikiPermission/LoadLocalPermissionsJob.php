<?php

namespace App\Jobs\WikiPermission;

use App\MwApi\MwApiExtras;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LoadLocalPermissionsJob extends BaseWikiPermissionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $wiki;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param string $wiki
     */
    public function __construct(User $user, string $wiki)
    {
        $this->user = $user;
        $this->wiki = $wiki;
    }

    public function getWikiId()
    {
        return $this->wiki;
    }

    public function getPermissionsToCheck()
    {
        return [
            'user',
            'sysop',
            'checkuser',
            'oversight',
        ];
    }

    public function checkIsBlocked()
    {
        return MwApiExtras::getBlockInfo($this->getWikiId(), $this->user->username) !== null;
    }
}
