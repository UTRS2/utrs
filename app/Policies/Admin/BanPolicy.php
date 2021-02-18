<?php

namespace App\Policies\Admin;

use App\Models\Ban;
use App\Models\User;
use App\Models\Wiki;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Beware: This policy is confusing, as it has methods which can handle cases like "can I do X on global bans",
 * and "can I do X anywhere", both of those do not have a wiki id or an object. :(
 *
 * Global bans are represented by wiki_id = null. Anything targeting "anywhere" is represented by self::WIKI_ANY
 * constant.
 *
 * If you change this code, make sure it is covered by automated tests and test carefully manually.
 */
class BanPolicy
{
    use HandlesAuthorization;

    const WIKI_ANY = 'any';
    const WIKI_GLOBAL = null;

    /**
     * Determine whether the user can view any bans.
     *
     * @param User $user
     * @param Wiki|string|null $wiki
     * @return mixed
     */
    public function viewAny(User $user, $wiki = self::WIKI_ANY)
    {
        if ($wiki === self::WIKI_ANY) {
            return $user->hasAnySpecifiedPermsOnAnyWiki('tooladmin');
        }

        if ($wiki === self::WIKI_GLOBAL) {
            return $user->hasAnySpecifiedLocalOrGlobalPerms([], 'tooladmin');
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
     * @param Wiki|null|string $wiki
     * @return mixed
     */
    public function create(User $user, $wiki = self::WIKI_ANY)
    {
        if ($wiki === self::WIKI_ANY) {
            return $user->hasAnySpecifiedPermsOnAnyWiki('tooladmin');
        }

        if ($wiki === self::WIKI_GLOBAL) {
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
     * @param Ban|string|int|array|null $target A ban object, an array of wiki names/ids or a wiki name/id,
     * or null or self::WIKI_ANY to check all known wikis.
     * @return mixed
     */
    public function oversight(User $user, $target = null)
    {
        // check if it can be done anywhere if requested
        if (!$target || $target === self::WIKI_ANY) {
            return $user->hasAnySpecifiedPermsOnAnyWiki(['oversight', 'steward', 'staff', 'developer']);
        }

        // check for the specified wiki(s)
        if (is_string($target) || is_int($target)) {
            $target = [$target];
        }

        if (is_string($target) || is_array($target)) {
            $target = collect($target)
                ->map(function ($value) {
                    // migrate wiki ids for database names
                    if (is_numeric($value)) {
                        // todo: do this as one query for all of the given ids
                        return Wiki::findOrFail($value)->database_name;
                    }

                    return $value;
                })
                ->toArray();

            return $user->hasAnySpecifiedLocalOrGlobalPerms($target, ['oversight', 'steward', 'staff', 'developer']);
        }

        // check for the specified ban
        $wikiDbName = $target->wiki ? $target->wiki->database_name : null;
        return $user->hasAnySpecifiedLocalOrGlobalPerms($wikiDbName, ['oversight', 'steward', 'staff', 'developer']);
    }
}
