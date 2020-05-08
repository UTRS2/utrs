<?php

namespace App\Jobs\WikiPermission;

use App\User;
use App\Permission;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\MediawikiFactory;

/**
 * Base job class to load user permissions from MediaWiki api.
 */
abstract class BaseWikiPermissionJob
{
    /** @var User */
    protected $user;

    /**
     * @return string
     */
    abstract function getWikiUrl();

    /**
     * @return string wiki in permissions table
     */
    abstract function getWikiId();

    /**
     * @return array
     */
    abstract function getPermissionsToCheck();

    protected function getUserPermissions()
    {
        $api = new MediawikiApi($this->getWikiUrl());
        $services = new MediawikiFactory($api);
        $user = $services->newUserGetter()->getFromUsername($this->user->username);

        // user does not exist
        if ($user->getId() === 0) {
            return [];
        }

        return $user->getGroups();
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

        $permObject->update($permissionsToUpdate);
    }
}
