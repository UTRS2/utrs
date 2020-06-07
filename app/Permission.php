<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Permission extends Model
{
    protected $guarded = ['id'];
    public $timestamps = false;

    const ALL_POSSIBILITIES = ['oversight', 'checkuser', 'steward', 'staff', 'developer', 'tooladmin', 'admin', 'user'];

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

        if ($wiki == "*") {
            $specific = Permission::where('userid', '=', $id)
                ->where('wiki', '=', '*')
                ->first();
        } else {
            $specific = Permission::where('userid', '=', $id)
                ->where('wiki', $wiki)
                ->orWhere('wiki', '*')
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

    public static function checkAdmin($id, $wiki)
    {
        return self::hasAnyPermission($id, $wiki, ['admin']) || self::hasAnyPermission($id, '*', ['steward', 'staff', 'developer']);
    }

    public static function checkToolAdmin($id, $wiki)
    {
        return self::hasAnyPermission($id, $wiki, ['tooladmin', 'oversight', 'checkuser']) || self::hasAnyPermission($id, '*', ['tooladmin', 'developer', 'steward', 'staff', 'developer']);
    }
}
