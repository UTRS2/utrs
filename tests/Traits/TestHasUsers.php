<?php

namespace Tests\Traits;

use App\Models\Permission;
use App\Models\User;

trait TestHasUsers
{
    protected $userDefaultPermissions = [
        'enwiki' => [
            'user', 'admin',
        ]
    ];

    protected function getUser($permissions = null, $extraData = [])
    {
        if (!$permissions) {
            $permissions = $this->userDefaultPermissions;
        }

        if (!array_key_exists('last_permission_check_at', $extraData)) {
            $extraData['last_permission_check_at'] = now();
        }

        User::unsetEventDispatcher(); // prevent loading user permissions, we'll do that manually

        $user = User::factory()->create($extraData);
        $wikis = [];

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

            if ($toSet['user']) {
                $wikis[] = $wiki;
            }
        }

        $user->wikis = implode(',', $wikis);
        $user->save();
        return $user;
    }

    protected function getTooladminUser($extraData = [])
    {
        $permissions = $this->userDefaultPermissions;
        $permissions['enwiki'][] = 'tooladmin';
        return $this->getUser($permissions, $extraData);
    }

    protected function getFunctionaryTooladminUser($extraData = [])
    {
        $permissions = $this->userDefaultPermissions;
        $permissions['enwiki'][] = 'tooladmin';
        $permissions['enwiki'][] = 'checkuser';
        $permissions['enwiki'][] = 'oversight';
        return $this->getUser($permissions, $extraData);
    }
}
