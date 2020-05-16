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
        // advanced filters on controller
        return $user->hasAnySpecifiedPermsOnAnyWiki(['user']);
    }

    /**
     * Determine whether the user can view the appeal.
     *
     * @param User $user
     * @param  \App\Appeal  $appeal
     * @return mixed
     */
    public function view(User $user, Appeal $appeal)
    {
        if (!$user->hasAnySpecifiedLocalOrGlobalPerms($appeal->wiki, ['user', 'sysop'])) {
            return false;
        }

        if ($appeal->status === 'INVALID') {
            // Developers can already see everything based on override in AuthServiceProvider
            return $this->deny('This appeal has been marked as invalid.');
        }

        if ($appeal->privacyreview !== $appeal->privacylevel || $appeal->privacylevel === 2) {
            return $user->hasAnySpecifiedLocalOrGlobalPerms($appeal->wiki, ['oversight', 'steward', 'staff', 'developer', 'privacy']) ? true
                : $this->deny('The appeal you are trying to access is currently being reviewed by the privacy team. You do not have sufficient permissions to view it.');
        }

        if ($appeal->privacylevel === 1) {
            return $user->hasAnySpecifiedLocalOrGlobalPerms($appeal->wiki, ['sysop']);
        }

        if (in_array($appeal->status, ['ACCEPT', 'DECLINE', 'EXPIRE', 'OPEN', 'PRIVACY', 'ADMIN', 'CHECKUSER'])) {
            return true;
        }

        return false;
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
     * @param  \App\Appeal  $appeal
     * @return mixed
     */
    public function update(User $user, Appeal $appeal)
    {
        Gate::authorize('view', $appeal);

        return $user->hasAnySpecifiedLocalOrGlobalPerms($appeal->wiki, ['sysop']) ? true
            : $this->deny('Only administrators can take actions on appeals.');
    }
}
