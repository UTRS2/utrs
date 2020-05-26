<?php

namespace Tests\Traits;

use App\User;
use App\Permission;
use Illuminate\Support\Arr;

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

        User::unsetEventDispatcher(); // prevent loading user permissions, we'll do that manually

        $user = factory(User::class)->create($extraData);
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
}
