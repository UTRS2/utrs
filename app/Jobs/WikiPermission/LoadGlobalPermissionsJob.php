<?php

namespace App\Jobs\WikiPermission;

use App\Models\User;
use App\Services\Facades\MediaWikiRepository;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mediawiki\DataModel\User as MediawikiUser;

class LoadGlobalPermissionsJob extends BaseWikiPermissionJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getPermissionWikiId()
    {
        return '*';
    }

    public function getPermissionsToCheck()
    {
        return [
            'user',
            'steward',
            'staff',
        ];
    }

    protected function getUserPermissions()
    {
        $groups = MediaWikiRepository::getGlobalApi()->getMediaWikiExtras()
            ->getGlobalGroupMembership($this->user->username);

        // grant global 'user' permission to stewards and staff
        if (in_array('steward', $groups) || in_array('staff', $groups)) {
            $groups[] = 'user';
        }

        return $groups;
    }
}
