<?php

namespace App;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use function foo\func;

class Permission extends Model
{
    protected $appends = ['presentPermissions'];
    protected $primaryKey = 'userid';
    public $timestamps = false;

    private $allPossibilities = ['oversight', 'checkuser', 'steward', 'staff', 'developer', 'tooladmin', 'privacy', 'admin', 'user'];

    public function getPresentPermissionsAttribute()
    {
        return collect($this->allPossibilities)
            ->filter(function ($possiblePerm) {
                return $this->$possiblePerm;
            });
    }

    /**
     * checks if this permission object has any of specified permissions present
     * @param array $perms permissions to check
     * @return boolean
     */
    public function hasAnyPerms(array $perms)
    {
        $perms = collect($perms)
            ->map(function ($string) {
                return Str::lower($string);
            });

        return $this->present_permissions
            ->intersect($perms)
            ->isNotEmpty();
    }

    public static function whoami($id,$wiki) {
        if(is_null($id)) {
            abort(403,'No logged in user');
        }
        if ($wiki=="*") {
            $specific = Permission::where('userid','=',$id)
                ->where('wiki','=','*')
                ->first();
            return $specific;
        }
        else {
            $specific = Permission::where('userid','=',$id)
                ->where('wiki', $wiki)
                ->orWhere('wiki', '*')
                ->first();
            return $specific;
        }
        abort(500,'Permissions Failure');
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
            if ($permission->{Str::lower($permissionName)} == 1) {
                return true;
            }
        }

        return false;
    }

    public static function checkSecurity($id, $level,$wiki) {
    	if(is_null($id)) {
    		abort(403,'No logged in user');
    	}
        if ($wiki=="*") {
            $specific = Permission::where('userid','=',$id)
                ->where('wiki','=','*')->first();
        }
    	else {
            $specific = Permission::where('userid','=',$id)
                ->where('wiki', $wiki)
                ->orWhere('wiki', '*')
                ->first();
        }
    	if ($level == "OVERSIGHT") {
    		if ($specific['oversight']==1) {return True;}
    		else {return False;}
    	}
    	if ($level == "CHECKUSER") {
    		if ($specific['checkuser']==1) {return True;}
    		else {return False;}
    	}
    	if ($level == "STEWARD") {
    		if ($specific['steward']==1) {return True;}
    		else {return False;}
    	}
    	if ($level == "STAFF") {
    		if ($specific['staff']==1) {return True;}
    		else {return False;}
    	}
    	if ($level == "DEVELOPER") {
    		if ($specific['developer']==1) {return True;}
    		else {return False;}
    	}
    	if ($level == "TOOLADMIN") {
    		if ($specific['tooladmin']==1) {return True;}
    		else {return False;}
    	}
    	if ($level == "PRIVACY") {
    		if ($specific['privacy']==1) {return True;}
    		else {return False;}
    	}
        if ($level == "ADMIN") {
            if ($specific['admin']==1) {return True;}
            else {return False;}
        }
    	if ($level == "USER") {
    		if ($specific['user']==1) {return True;}
    		else {return False;}
    	}
    }

    public static function checkCheckuser($id,$wiki) {
        return self::hasAnyPermission($id, $wiki, ['checkuser']) || self::hasAnyPermission($id, '*', ['steward', 'staff', 'developer']);
    }

    public static function checkOversight($id,$wiki) {
        return self::hasAnyPermission($id, $wiki, ['oversight']) || self::hasAnyPermission($id, '*', ['steward', 'staff', 'developer']);
    }

    public static function checkPrivacy($id,$wiki) {
        return self::hasAnyPermission($id, $wiki, ['oversight']) || self::hasAnyPermission($id, '*', ['steward', 'staff', 'developer', 'privacy']);
    }

    public static function checkAdmin($id,$wiki) {
        return self::hasAnyPermission($id, $wiki, ['admin']) || self::hasAnyPermission($id, '*', ['steward', 'staff', 'developer', 'privacy']);
    }

    public static function checkToolAdmin($id,$wiki) {
        return self::hasAnyPermission($id, $wiki, ['tooladmin', 'oversight', 'checkuser']) || self::hasAnyPermission($id, '*', ['developer', 'steward', 'staff', 'developer', 'privacy']);
    }
}
