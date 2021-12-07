<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeed extends Seeder
{
    public function run()
    {
        $createUser = env('DEVELOPER_USER_NAME');
        if (!$createUser) {
            return;
        }

        $user = User::withoutEvents(function () use ($createUser) {
            return User::create([
                'username' => $createUser,
                'last_permission_check_at' => now(),
            ]);
        });

        $grantAdmin = env('DEVELOPER_USER_GRANT_ADMIN','');
        $grantTooladmin = env('DEVELOPER_USER_GRANT_TOOLADMIN', '');
        $grantDeveloper = env('DEVELOPER_USER_GRANT_DEVELOPER');

        if ($grantDeveloper) {
            Permission::create([
                'user_id' => $user->id,
                'developer' => 1,
                'wiki' => '*',
            ]);
        }

        $this->grant($user, 'admin', $grantAdmin);
        $this->grant($user, 'tooladmin', $grantTooladmin);
    }

    private function grant(User $user, string $permission, string $wikis)
    {
        foreach (explode(' ', $wikis) as $wiki) {
            Permission::firstOrCreate([
                'user_id' => $user->id,
                'wiki' => $wiki,
            ])->update([
                $permission => 1,
                'user' => 1,
            ]);
        }
    }
}
