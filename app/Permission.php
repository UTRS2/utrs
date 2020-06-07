<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Permission extends Model
{
    protected $appends = ['presentPermissions', 'wikiFormKey'];
    protected $guarded = ['id'];
    public $timestamps = false;

    const ALL_POSSIBILITIES = ['oversight', 'checkuser', 'steward', 'staff', 'developer', 'tooladmin', 'admin', 'user'];

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

    public function user()
    {
        return $this->belongsTo(User::class, 'userid');
    }

    /**
     * checks if this permission object has any of specified permissions present
     * @param array $perms permissions to check
     * @return boolean
     */
    public function hasAnySpecifiedPerms(array $perms)
    {
        $perms = collect($perms)
            ->map(function ($string) {
                return Str::lower($string);
            });

        return $this->present_permissions
            ->intersect($perms)
            ->isNotEmpty();
    }

    public static function whoami($id, $wiki)
    {
        abort_if(is_null($id), 'No logged in user');

        if ($wiki === "*") {
            return Permission::where('userid', '=', $id)
                ->where('wiki', '=', '*')
                ->first();
        }

        return Permission::where('userid', '=', $id)
            ->where('wiki', $wiki)
            ->orWhere('wiki', '*')
            ->first();
    }

    public static function hasAnyPermission($userId, $wiki, $permissionArray)
    {
        abort_if(is_null($userId), 403, 'No logged in user');
        $permission = Permission::where('userid', $userId)
            ->where('wiki', $wiki)
            ->first();

        if (!$permission) {
            return false;
        }

        foreach ($permissionArray as $permissionName) {
            if ($permission->{Str::lower($permissionName)}) {
                return true;
            }
        }

        return false;
    }

    public static function checkSecurity($id, $level, $wiki)
    {
        abort_if(is_null($id), 403, 'No logged in user');

        if ($wiki === '*' || $wiki === 'global') {
            $specific = Permission::where('userid', '=', $id)
                ->where('wiki', '=', '*')
                ->first();
        } else {
            if (self::checkSecurity($id, $level, '*')) {
                return true;
            }

            $specific = Permission::where('userid', '=', $id)
                ->where('wiki', $wiki)
                ->first();
        }

        if (!$specific) {
            return false;
        }

        if ($level == "OVERSIGHT") {
            return $specific->oversight;
        }
        if ($level == "CHECKUSER") {
            return $specific->checkuser;
        }
        if ($level == "STEWARD") {
            return $specific->steward;
        }
        if ($level == "STAFF") {
            return $specific->staff;
        }
        if ($level == "DEVELOPER") {
            return $specific->developer;
        }
        if ($level == "TOOLADMIN") {
            return $specific->tooladmin;
        }
        if ($level == "PRIVACY") {
            return $specific->privacy;
        }
        if ($level == "ADMIN") {
            return $specific->admin;
        }
        if ($level == "USER") {
            return $specific->user;
        }

        return false;
    }

    public static function checkCheckuser($id, $wiki)
    {
        return self::hasAnyPermission($id, $wiki, ['checkuser']) || self::hasAnyPermission($id, '*', ['steward', 'staff', 'developer']);
    }

    public static function checkOversight($id, $wiki)
    {
        return self::hasAnyPermission($id, $wiki, ['oversight']) || self::hasAnyPermission($id, '*', ['steward', 'staff', 'developer']);
    }

    public static function checkPrivacy($id, $wiki)
    {
        return self::hasAnyPermission($id, $wiki, ['oversight']) || self::hasAnyPermission($id, '*', ['steward', 'staff', 'developer', 'privacy']);
    }

    public static function checkAdmin($id, $wiki)
    {
        return self::hasAnyPermission($id, $wiki, ['admin']) || self::hasAnyPermission($id, '*', ['steward', 'staff', 'developer', 'privacy']);
    }

    public static function checkToolAdmin($id, $wiki)
    {
        return self::hasAnyPermission($id, $wiki, ['tooladmin', 'oversight', 'checkuser']) || self::hasAnyPermission($id, '*', ['tooladmin', 'developer', 'steward', 'staff', 'developer', 'privacy']);
    }
}
