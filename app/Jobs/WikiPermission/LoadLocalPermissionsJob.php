<?php

namespace App\Jobs\WikiPermission;

use App\Models\User;
use App\Services\Facades\MediaWikiRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mediawiki\DataModel\User as MediawikiUser;

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

    public function shouldHaveUser(MediawikiUser $user, array $groups)
    {
        return in_array('sysop', $groups);
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
        return MediaWikiRepository::getApiForTarget($this->getPermissionWikiId())->getMediaWikiExtras()->getBlockInfo($this->user->username) !== null;
    }
}
