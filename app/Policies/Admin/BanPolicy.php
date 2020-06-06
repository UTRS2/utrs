<?php

namespace App\Policies\Admin;

use App\Ban;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BanPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any bans.
     *
     * @param User $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasAnySpecifiedPermsOnAnyWiki(['tooladmin']);
    }

    /**
     * Determine whether the user can view the ban.
     *
     * @param User $user
     * @param Ban $ban
     * @return mixed
     */
    public function view(User $user, Ban $ban)
    {
        return $user->hasAnySpecifiedPermsOnAnyWiki(['tooladmin']);
    }

    /**
     * Determine whether the user can view the name of the banned user.
     *
     * @param User $user
     * @param Ban $ban
     * @return bool
     */
    public function viewName(User $user, Ban $ban)
    {
        if (!$ban->is_protected) {
            return true;
        }

        return $user->hasAnySpecifiedPermsOnAnyWiki(['oversight', 'steward', 'staff', 'developer']);
    }

    /**
     * Determine whether the user can create bans.
     *
     * @param User $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasAnySpecifiedPermsOnAnyWiki(['tooladmin']);
    }

    /**
     * Determine whether the user can update the ban.
     *
     * @param User $user
     * @param Ban $ban
     * @return mixed
     */
    public function update(User $user, Ban $ban)
    {
        return $user->hasAnySpecifiedPermsOnAnyWiki(['tooladmin']);
    }

    /**
     * Determine whether the user can delete the ban.
     *
     * @param User $user
     * @param Ban $ban
     * @return mixed
     */
    public function delete(User $user, Ban $ban)
    {
        return $user->hasAnySpecifiedPermsOnAnyWiki(['tooladmin']);
    }
}
