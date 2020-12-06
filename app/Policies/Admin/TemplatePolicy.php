<?php

namespace App\Policies\Admin;

use App\Models\Wiki;
use App\Models\Template;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TemplatePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any templates.
     *
     * @param User $user
     * @param Wiki $wiki
     * @return mixed
     */
    public function viewAny(User $user, Wiki $wiki)
    {
        return $user->hasAnySpecifiedLocalOrGlobalPerms($wiki->database_name, 'tooladmin');
    }

    /**
     * Determine whether the user can view the template.
     *
     * @param User $user
     * @param Template $template
     * @return mixed
     */
    public function view(User $user, Template $template)
    {
        return $user->hasAnySpecifiedLocalOrGlobalPerms($template->wiki->database_name, 'tooladmin');
    }

    /**
     * Determine whether the user can create templates.
     *
     * @param User $user
     * @param Wiki $wiki
     * @return mixed
     */
    public function create(User $user, Wiki $wiki)
    {
        return $user->hasAnySpecifiedLocalOrGlobalPerms($wiki->database_name, 'tooladmin');
    }

    /**
     * Determine whether the user can update the template.
     *
     * @param User $user
     * @param Template $template
     * @return mixed
     */
    public function update(User $user, Template $template)
    {
        return $user->hasAnySpecifiedLocalOrGlobalPerms($template->wiki->database_name, 'tooladmin');
    }

    /**
     * Determine whether the user can delete the template.
     *
     * @param User $user
     * @param Template $template
     * @return mixed
     */
    public function delete(User $user, Template $template)
    {
        return $user->hasAnySpecifiedLocalOrGlobalPerms($template->wiki->database_name, 'tooladmin');
    }
}
