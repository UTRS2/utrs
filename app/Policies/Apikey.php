<?php

namespace App\Policies;

use App\Models\User;

class Apikey
{
    public function viewAny(User $user): bool
    {
        // is apiadmin or developer
        return $user->hasPermission('apiadmin') || $user->hasPermission('developer');
    }

    public function create(User $user): bool
    {
        // is apiadmin or developer
        return $user->hasPermission('apiadmin') || $user->hasPermission('developer');
    }

    public function revoke(User $user): bool
    {
        // is apiadmin or developer
        return $user->hasPermission('apiadmin') || $user->hasPermission('developer');
    }
}
