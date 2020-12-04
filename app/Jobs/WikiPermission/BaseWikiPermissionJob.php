<?php

namespace App\Jobs\WikiPermission;

use App\Models\Permission;
use App\Models\User;
use App\Services\Facades\MediaWikiRepository;
use Mediawiki\DataModel\User as MediawikiUser;

/**
 * Base job class to (re)load user permissions from MediaWiki api.
 */
abstract class BaseWikiPermissionJob
{
    /** @var User */
    protected $user;

    /**
     * @return string wiki in permissions table
     */
    abstract function getPermissionWikiId();

    /**
     * @return array list of mediawiki group names that should be checked for the user in this wiki
     */
    abstract function getPermissionsToCheck();

    /**
     * If necessary, change group values gotten from the MediaWiki API before writing them into the database
     * @param  string $groupName user group in MediaWiki side
     * @return string            column name in {@link Permission}
     */
    public function getGroupName(string $groupName)
    {
        return $groupName;
    }

    /**
     * Get list of permissions this user definitely has
     * @return array Array of column names of permissions this user has
     */
    protected abstract function getUserPermissions();

    /**
     * Run the job.
     */
    public function handle()
    {
        $permissions = $this->getUserPermissions();
        $permissionsToUpdate = collect($this->getPermissionsToCheck())
            ->mapWithKeys(function ($permissionName) use ($permissions) {
                return [$this->getGroupName($permissionName) => in_array($permissionName, $permissions) ? 1 : 0];
            })
            ->toArray();

        $searchValues = [
            'wiki' => $this->getPermissionWikiId(),
            'userid' => $this->user->id,
        ];

        $permObject = Permission::firstOrNew($searchValues);

        // if user does not have a permission object for this wiki and they don't need one, let's not make one
        if (!$permObject->exists && !in_array('user', $permissions)) {
            return;
        }

        $permObject->fill($permissionsToUpdate)->saveOrFail();
    }
}
