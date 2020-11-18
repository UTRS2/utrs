<?php

namespace App\Policies\Admin;

use App\Models\Sitenotice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SiteNoticePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any sitenotices.
     *
     * @param User $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasAnySpecifiedPermsOnAnyWiki(['tooladmin']);
    }

    /**
     * Determine whether the user can view the sitenotice.
     *
     * @param User $user
     * @param Sitenotice $sitenotice
     * @return mixed
     */
    public function view(User $user, Sitenotice $sitenotice)
    {
        return $user->hasAnySpecifiedPermsOnAnyWiki(['tooladmin']);
    }

    /**
     * Determine whether the user can create sitenotices.
     *
     * @param User $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasAnySpecifiedPermsOnAnyWiki(['tooladmin']);
    }

    /**
     * Determine whether the user can update the sitenotice.
     *
     * @param User $user
     * @param Sitenotice $sitenotice
     * @return mixed
     */
    public function update(User $user, Sitenotice $sitenotice)
    {
        return $user->hasAnySpecifiedPermsOnAnyWiki(['tooladmin']);
    }

    /**
     * Determine whether the user can delete the sitenotice.
     *
     * @param User $user
     * @param Sitenotice $sitenotice
     * @return mixed
     */
    public function delete(User $user, Sitenotice $sitenotice)
    {
        return $user->hasAnySpecifiedPermsOnAnyWiki(['tooladmin']);
    }
}
