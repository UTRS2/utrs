<?php

namespace App\Policies\Admin;

use App\Models\Ban;
use App\Models\User;
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

        return $this->oversight($user, $ban);
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

    /**
     * Determine whether the user can hide the ban target from public view.
     *
     * @param User $user
     * @param Ban $ban
     * @return mixed
     */
    public function oversight(User $user, ?Ban $ban = null)
    {
        return $user->hasAnySpecifiedPermsOnAnyWiki(['oversight', 'steward', 'staff', 'developer']);
    }
}
