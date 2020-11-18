<?php

namespace App\Jobs\WikiPermission;

use App\Models\Permission;
use App\Models\User;
use App\MwApi\MwApiGetter;
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
     * Function to remove "user" group from this user if specific conditions are met
     * @param  MediawikiUser $user   mediawiki user object
     * @param  array         $groups array of permission database names this user has
     * @return boolean               if false, the 'user' group will be removed
     */
    abstract function shouldHaveUser(MediawikiUser $user, array $groups);

    /**
     * @return bool true if {@link $user} is blocked on this wiki, false otherwise
     */
    abstract function checkIsBlocked();

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
     * Validate if the tool user should be authorized to have the user permission
     * @param  MediawikiUser $user   An instance of MediawikiUser related to the onwiki users
     * @param  array         $groups The groups to filter through
     * @return array                 The final list of groups
     */
    protected function validateToolUserPermission(MediawikiUser $user, array $groups)
    {
        // drop user group if user is blocked or has less than 500 edits
        if (!$this->shouldHaveUser($user, $groups) || $this->checkIsBlocked() || $user->getEditcount() < 500) {
            $groups = array_values(array_filter($groups, function ($group) { return $group !== 'user'; }));
        }

        return $groups;
    }

    /**
     * Get list of permissions this user definitely has
     * @return array Array of column names of permissions this user has
     */
    protected function getUserPermissions()
    {
        $services = MwApiGetter::getServicesForWiki($this->getPermissionWikiId());
        $user = $services->newUserGetter()->getFromUsername($this->user->username);

        // user does not exist
        if ($user->getId() === 0) {
            return [];
        }

        return $this->validateToolUserPermission($user, $user->getGroups());
    }

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
