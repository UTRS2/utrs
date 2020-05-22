<?php

namespace App\Jobs\WikiPermission;

use App\User;
use App\Permission;
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
    abstract function getWikiId();

    /**
     * @return array list of groups that should be checked for the user in this wiki
     */
    abstract function getPermissionsToCheck();

    /**
     * @return bool true if {@link $user} is blocked on this wiki, false otherwise
     */
    abstract function checkIsBlocked();

    /**
     * if necessary, change group values gotten from the MediaWiki API before writing them into the database
     * @param string $groupName group in MediaWiki side
     * @return string column name in {@link Permission}
     */
    public function getGroupName(string $groupName)
    {
        return $groupName;
    }

    /**
     * @return string wiki name on users.wikis column
     */
    protected function getValueInAllowedWikis()
    {
        return $this->getWikiId();
    }

    protected function transformGroupArray(MediawikiUser $user, array $groups)
    {
        // drop user group if user is blocked or has less than 500 edits
        if ($this->checkIsBlocked() || $user->getEditcount() < 500) {
            $groups = array_values(array_filter($groups, function ($group) { return $group !== 'user'; }));
        }

        return $groups;
    }

    /**
     * @param bool $exists true if this user exists on the wiki this job is querying
     */
    public function updateDoesExist(bool $exists)
    {
        $wikis = explode(',', $this->user->wikis ?? '');
        $wikiId = $this->getValueInAllowedWikis();

        if ($exists) {
            array_push($wikis, $wikiId);
        } else {
            // according to stackoverflow this is the best way to remove an element from an array
            $wikis = array_values(array_filter($wikis, function($value) use ($wikiId) { return $value !== $wikiId; }));
        }

        $this->user->wikis = implode(',', $wikis);
    }

    protected function getUserPermissions()
    {
        $services = MwApiGetter::getServicesForWiki($this->getWikiId());
        $user = $services->newUserGetter()->getFromUsername($this->user->username);

        // user does not exist
        if ($user->getId() === 0) {
            return [];
        }

        $groups = array_map('self::getGroupName', $user->getGroups());
        return $this->transformGroupArray($user, $groups);
    }

    /**
     * Run the job.
     */
    public function handle()
    {
        $permissions = $this->getUserPermissions();
        $permissionsToUpdate = collect($this->getPermissionsToCheck())
            ->mapWithKeys(function ($permissionName) use ($permissions) {
                return [$permissionName => in_array($permissionName, $permissions) ? 1 : 0];
            })
            ->toArray();

        $permObject = Permission::firstOrNew([
            'wiki' => $this->getWikiId(),
            'user' => $this->user->id,
        ]);

        // if user does not have a permission object for this wiki and they don't need one, let's not make one
        if (!$permObject->exists && !in_array('user', $permissions)) {
            return;
        }

        $this->updateDoesExist(in_array('user', $permissions));
        $permObject->update($permissionsToUpdate);
    }
}
