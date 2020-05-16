<?php

namespace App\Jobs\WikiPermission;

use App\User;
use App\Permission;
use App\MwApi\MwApiGetter;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\MediawikiFactory;
use Mediawiki\DataModel\User as MediawikiUser;

/**
 * Base job class to load user permissions from MediaWiki api.
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
     * @return string wiki name on users.wikis column
     */
    protected function getValueInAllowedWikis()
    {
        return $this->getWikiId();
    }

    protected function transformGroupArray(MediawikiUser $user, array $groups)
    {
        // drop user group if user has less than 500 edits
        if ($user->getEditcount() < 500) {
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

        return $this->transformGroupArray($user, $user->getGroups());
    }

    /**
     * Run the job.
     */
    public function handle()
    {
        $permissions = $this->getUserPermissions();
        $permissionsToUpdate = array_map(function ($permission) use ($permissions) {
            return in_array($permission, $permissions) ? 1 : 0;
        }, $this->getPermissionsToCheck());

        $permObject = Permission::firstOrNew([
            'wiki' => $this->getWikiId(),
            'user' => $this->user->id,
        ]);

        if (!$permObject->exists && !in_array('user', $permissions)) {
            return;
        }

        $this->updateDoesExist(in_array('user', $permissions));
        $permObject->update($permissionsToUpdate);
    }
}
