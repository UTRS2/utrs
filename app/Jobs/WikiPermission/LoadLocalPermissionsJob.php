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

class LoadLocalPermissionsJob extends BaseWikiPermissionJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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

    public function getPermissionWikiId()
    {
        return $this->wiki;
    }

    public function getGroupName(string $groupName)
    {
        if ($groupName === 'sysop') {
            return 'admin';
        }

        return parent::getGroupName($groupName);
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
        return MediaWikiRepository::getApiForTarget($this->getPermissionWikiId())
                ->getMediaWikiExtras()->getBlockInfo($this->user->username) !== null;
    }

    protected function validateToolUserPermission(MediawikiUser $user, array $groups)
    {
        // drop 'user' group for blocked users and non-sysops
        if (!in_array('sysop', $groups) || $this->checkIsBlocked()) {
            $groups = array_values(array_filter($groups, function ($group) { return $group !== 'user'; }));
        }

        return $groups;
    }

    protected function getUserPermissions()
    {
        $services = MediaWikiRepository::getApiForTarget($this->getPermissionWikiId())->getAddWikiServices();
        $user = $services->newUserGetter()->getFromUsername($this->user->username);

        // user does not exist
        if ($user->getId() === 0) {
            return [];
        }

        return $this->validateToolUserPermission($user, $user->getGroups());
    }
}
