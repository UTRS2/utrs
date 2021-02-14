<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class WikiPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the list of stored wiki objects.
     *
     * @param User $user
     * @return boolean
     */
    public function viewAny(User $user)
    {
        return true;
    }
}
