<?php

namespace Tests\Traits;

use App\Models\Permission;
use App\Models\User;

trait TestHasUsers
{
    protected $userDefaultPermissions = [
        'enwiki' => [ // MediaWikiRepository::getSupportedTargets()[0]
            'user', 'admin',
        ]
    ];

    protected function getUser($permissions = null, $extraData = []): User
    {
        if (!$permissions) {
            $permissions = $this->userDefaultPermissions;
        }

        if (!array_key_exists('last_permission_check_at', $extraData)) {
            $extraData['last_permission_check_at'] = now();
        }

        // prevent loading user permissions, we'll do that manually
        $dispatcher = User::getEventDispatcher();
        User::unsetEventDispatcher();
        $user = User::factory()->create($extraData);
        User::setEventDispatcher($dispatcher);

        foreach ($permissions as $wiki => $values) {
            $toSet = collect(Permission::ALL_POSSIBILITIES)
                ->mapWithKeys(function ($permName) use ($values) {
                    return [$permName => in_array($permName, $values) ? 1 : 0];
                })
                ->toArray();

            Permission::firstOrCreate([
                'userid' => $user->id,
                'wiki' => $wiki,
            ], $toSet);
        }

        return $user;
    }

    protected function getTooladminUser($extraData = []): User
    {
        $permissions = $this->userDefaultPermissions;
        $permissions['enwiki'][] = 'tooladmin';
        return $this->getUser($permissions, $extraData);
    }

    protected function getFunctionaryTooladminUser($extraData = []): User
    {
        $permissions = $this->userDefaultPermissions;
        $permissions['enwiki'][] = 'tooladmin';
        $permissions['enwiki'][] = 'checkuser';
        $permissions['enwiki'][] = 'oversight';
        return $this->getUser($permissions, $extraData);
    }

    protected function getDeveloperUser($extraData = []): User
    {
        $permissions = $this->userDefaultPermissions;
        $permissions['*'] = ['developer'];
        return $this->getUser($permissions, $extraData);
    }
}
