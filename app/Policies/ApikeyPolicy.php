<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class ApikeyPolicy
{
    use HandlesAuthorization;

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
