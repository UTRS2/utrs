<?php

namespace App\Policies\Admin;

use App\User;
use Illuminate\Support\Arr;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasAnySpecifiedPermsOnAnyWiki(['tooladmin']);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param User $model
     * @return mixed
     */
    public function view(User $user, User $model)
    {
        return $user->hasAnySpecifiedPermsOnAnyWiki(['tooladmin']);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasAnySpecifiedPermsOnAnyWiki(['tooladmin']);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param User $model
     * @return mixed
     */
    public function update(User $user, User $model)
    {
        return $user->hasAnySpecifiedPermsOnAnyWiki(['tooladmin']);
    }

    public function updatePermission(User $user, User $model, string $wiki, string $permission)
    {
        $permissionsNeeded = [
            'developer' => ['developer'], // only developers can assign developer permissions
            'tooladmin' => ['tooladmin', 'developer'], // tooladmins can make other people to tooladmins,
            'privacy' => ['developer'], // TODO: this should allow users with both tooladmin and privacy rights should be able to assign privacy
        ];

        if (!Arr::exists($permissionsNeeded, $permission)) {
            return false;
        }

        return $user->hasAnySpecifiedLocalOrGlobalPerms($wiki, $permissionsNeeded[$permission]);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param User $model
     * @return mixed
     */
    public function delete(User $user, User $model)
    {
        return $user->hasAnySpecifiedPermsOnAnyWiki(['tooladmin']);
    }
}