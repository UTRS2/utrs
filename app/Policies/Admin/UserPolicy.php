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
        if ($user->id === $model->id) {
            // allow user to view their own profile
            return true;
        }

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
        return false;
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
        if ($user->id === $model->id) {
            // allow user to view their own profile; currently this only means that they can refresh their permissions
            // if adding more capabilities for user profiles in the future, please make sure that this won't create any security issues
            return true;
        }

        return $user->hasAnySpecifiedPermsOnAnyWiki(['tooladmin']);
    }

    public function updatePermission(User $user, User $model, string $wiki, string $permission)
    {
        $permissionsNeeded = [
            'developer' => ['developer'], // only developers can assign developer permissions
            'tooladmin' => ['tooladmin', 'developer'], // tooladmins can make other people to tooladmins,
            'staff' => ['developer'], // developers can assign staff rights
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
