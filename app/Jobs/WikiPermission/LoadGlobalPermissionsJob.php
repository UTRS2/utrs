<?php

namespace App\Jobs\WikiPermission;

use App\MwApi\MwApiExtras;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mediawiki\DataModel\User as MediawikiUser;

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

    public function shouldHaveUser(MediawikiUser $user, array $groups)
    {
        return in_array('steward', $groups) || in_array('staff', $groups);
    }

    public function getPermissionsToCheck()
    {
        return [
            'user',
            'steward',
            'staff',
        ];
    }

    public function checkIsBlocked()
    {
        return MwApiExtras::getGlobalBlockInfo($this->user->username) !== null;
    }
}
