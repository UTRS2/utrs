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
        return $user->hasAnySpecifiedPermsOnAnyWiki('apiadmin') || $user->hasAnySpecifiedPermsOnAnyWiki('developer');
    }

    public function create(User $user): bool
    {
        // is apiadmin or developer
        return $user->hasAnySpecifiedPermsOnAnyWiki('apiadmin') || $user->hasAnySpecifiedPermsOnAnyWiki('developer');
    }

    public function revoke(User $user): bool
    {
        // is apiadmin or developer
        return $user->hasAnySpecifiedPermsOnAnyWiki('apiadmin') || $user->hasAnySpecifiedPermsOnAnyWiki('developer');
    }
}
