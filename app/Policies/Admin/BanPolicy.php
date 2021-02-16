<?php

namespace App\Policies\Admin;

use App\Models\Ban;
use App\Models\User;
use App\Models\Wiki;
use Illuminate\Auth\Access\HandlesAuthorization;

class BanPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any bans.
     *
     * @param User $user
     * @param Wiki|int|null $wiki
     * @return mixed
     */
    public function viewAny(User $user, $wiki = null)
    {
        // horrible hack
        if ($wiki === 0) {
            return $user->hasAnySpecifiedLocalOrGlobalPerms([], 'tooladmin');
        }

        if (!$wiki) {
            return $user->hasAnySpecifiedPermsOnAnyWiki('tooladmin');
        }

        return $user->hasAnySpecifiedLocalOrGlobalPerms($wiki->database_name, 'tooladmin');
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
        $wikiDbName = $ban->wiki ? $ban->wiki->database_name : null;
        return $user->hasAnySpecifiedLocalOrGlobalPerms($wikiDbName, 'tooladmin');
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
     * @param Wiki $wiki
     * @return mixed
     */
    public function create(User $user, ?Wiki $wiki)
    {
        if (!$wiki) {
            return $user->hasAnySpecifiedLocalOrGlobalPerms([], 'tooladmin');
        }

        return $user->hasAnySpecifiedLocalOrGlobalPerms($wiki->database_name, 'tooladmin');
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
        $wikiDbName = $ban->wiki ? $ban->wiki->database_name : null;
        return $user->hasAnySpecifiedLocalOrGlobalPerms($wikiDbName, 'tooladmin');
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
        $wikiDbName = $ban->wiki ? $ban->wiki->database_name : null;
        return $user->hasAnySpecifiedLocalOrGlobalPerms($wikiDbName, 'tooladmin');
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
        $wikiDbName = $ban->wiki ? $ban->wiki->database_name : null;
        return $user->hasAnySpecifiedLocalOrGlobalPerms($wikiDbName, ['oversight', 'steward', 'staff', 'developer']);
    }
}
