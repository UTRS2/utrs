<?php

namespace App\Policies;

use App\Models\LogEntry;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LogEntryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the log entry.
     *
     * @param User|null $user
     * @param LogEntry $log
     * @return mixed
     */
    public function view(?User $user, LogEntry $log)
    {
        if ($log->protected == LogEntry::LOG_PROTECTION_NONE) {
            return true;
        }

        if (!$user) {
            return false;
        }

        $wiki = $log->tryFigureAssociatedWiki();
        $validPermissions = $log->protected == LogEntry::LOG_PROTECTION_FUNCTIONARY
            ? ['checkuser', 'oversight', 'steward', 'staff', 'developer']
            : ['admin', 'steward', 'staff', 'developer'];

        // if we can figure out the wiki, that's great, otherwise just check if the permission is present anywhere
        return $wiki
            ? $user->hasAnySpecifiedLocalOrGlobalPerms($wiki, $validPermissions)
            : $user->hasAnySpecifiedPermsOnAnyWiki($validPermissions);
    }
}
