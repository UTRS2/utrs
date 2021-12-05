<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Permission extends Model
{
    protected $appends = ['presentPermissions', 'wikiFormKey'];
    protected $guarded = ['id'];
    public $timestamps = false;

    const ALL_POSSIBILITIES = ['checkuser', 'oversight', 'steward', 'staff', 'developer', 'tooladmin', 'admin', 'user'];

    public function getPresentPermissionsAttribute()
    {
        return collect(self::ALL_POSSIBILITIES)
            ->filter(function ($possiblePerm) {
                return $this->$possiblePerm;
            });
    }

    // this is a really stupid hack; but laravel's request()->input('...') doesn't really like form keys with *'s.
    public function getWikiFormKeyAttribute()
    {
        return $this->wiki === '*' ? 'global' : $this->wiki;
    }

    public function userObject()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * checks if this permission object has any of specified permissions present
     * @param array $perms permissions to check
     * @return boolean
     */
    public function hasAnySpecifiedPerms(array $perms)
    {
        // magic array. allows value permissions instead of key
        // for example 'admin' => ['steward', 'staff'], allows users with
        // 'staff' or 'steward' permission to do actions that check for 'admin' permission

        $alternatives = [
            'admin' => ['steward', 'staff'],
        ];

        $perms = collect($perms)
            ->map(function ($string) {
                return Str::lower($string);
            })
            ->flatMap(function ($string) use ($alternatives) {
                return array_key_exists($string, $alternatives)
                    ? array_merge([$string], $alternatives[$string])
                    : [$string];
            });

        return $this->present_permissions
            ->intersect($perms)
            ->isNotEmpty();
    }

    /** @deprecated use {@link Permission::hasAnySpecifiedPerms()} instead, or use policies */
    public static function hasAnyPermission($userId, $wiki, $permissionArray)
    {
        abort_if(is_null($userId), 403, 'No logged in user');
        return User::findOrFail($userId)->hasAnySpecifiedLocalOrGlobalPerms($wiki, $permissionArray);
    }

    /** @deprecated use {@link User::hasAnySpecifiedLocalOrGlobalPerms()} instead, or use policies */
    public static function checkCheckuser($id, $wiki)
    {
        return self::hasAnyPermission($id, $wiki, ['checkuser']) || self::hasAnyPermission($id, '*', ['steward', 'staff', 'developer']);
    }

    /** @deprecated use {@link User::hasAnySpecifiedLocalOrGlobalPerms()} instead, or use policies */
    public static function checkAdmin($id, $wiki)
    {
        return self::hasAnyPermission($id, $wiki, ['admin']) || self::hasAnyPermission($id, '*', ['steward', 'staff', 'developer']);
    }
}
