<?php

namespace App\Policies;

use App\Appeal;
use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppealPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any appeals.
     *
     * @param User $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        // filters on controller
        return $user->hasAnySpecifiedPermsOnAnyWiki(['user']);
    }

    /**
     * Determine whether the user can view the appeal.
     *
     * @param User $user
     * @param Appeal $appeal
     * @return mixed
     */
    public function view(User $user, Appeal $appeal)
    {
        if (!$user->hasAnySpecifiedLocalOrGlobalPerms($appeal->wiki, ['admin'])) {
            return $this->deny('Only ' . $appeal->wiki . ' administrators are able to see this appeal.');
        }

        if ($appeal->status === Appeal::STATUS_INVALID) {
            // Developers can already see everything based on override in AuthServiceProvider
            return $this->deny('This appeal has been marked as invalid.');
        }

        // view also has some filters
        return !in_array($appeal->status, Appeal::REGULAR_NO_VIEW_STATUS);
    }

    /**
     * Determine whether the user can create appeals.
     *
     * @param User $user
     * @return mixed
     */
    public function create(?User $user)
    {
        if ($user) {
            return $this->deny('You are attempting to file an appeal while logged in to the system. Please logout to file an appeal.');
        }

        return true;
    }

    /**
     * Determine whether the user can update the appeal.
     *
     * @param User $user
     * @param Appeal $appeal
     * @return mixed
     */
    public function update(User $user, Appeal $appeal)
    {
        Gate::authorize('view', $appeal);

        return $user->hasAnySpecifiedLocalOrGlobalPerms($appeal->wiki, ['admin']) ? true
            : $this->deny('Only administrators can take actions on appeals.');
    }

    /**
     * Determine whether the user can update take developer actions on this appeal.
     *
     * @param User $user
     * @param Appeal $appeal
     * @return mixed
     */
    public function performDeveloperActions(User $user, Appeal $appeal)
    {
        // Handle via Gate::before()
        return $this->deny('Only developers can take developer actions on appeals.');
    }
}
