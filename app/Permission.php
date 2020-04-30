<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $primaryKey = 'userid';
    public $timestamps = false;

    public static function whoami($id,$wiki) {
        if(is_null($id)) {
            abort(403,'No logged in user');
        }
        if ($wiki=="*") {
            $specific = Permission::where('userid','=',$id)->where('wiki','=','*')->get()->first();
            return $specific;
        }
        else {
            $specific = Permission::where('userid','=',$id)->where('wiki','rlike','\\*|'.$wiki)->get()->first();
            return $specific;
        }
        abort(500,'Permissions Failure');
    }
    public static function checkSecurity($id, $level,$wiki) {
    	if(is_null($id)) {
    		abort(403,'No logged in user');
    	}
        if ($wiki=="*") {
            $specific = Permission::where('userid','=',$id)->where('wiki','=','*')->get()->first();
        }
    	else {
            $specific = Permission::where('userid','=',$id)->where('wiki','rlike','\\*|'.$wiki)->get()->first();
        }

        dd($specific);
    	if ($level == "OVERSIGHT") {
    		if (isset($specific->oversight)) {return True;}
    		else {return False;}
    	}
    	if ($level == "CHECKUSER") {
    		if (isset($specific->checkuser)) {return True;}
    		else {return False;}
    	}
    	if ($level == "STEWARD") {
    		if (isset($specific->steward)) {return True;}
    		else {return False;}
    	}
    	if ($level == "STAFF") {
    		if (isset($specific->staff)) {return True;}
    		else {return False;}
    	}
    	if ($level == "DEVELOPER") {
    		if (isset($specific->developer)) {return True;}
    		else {return False;}
    	}
    	if ($level == "TOOLADMIN") {
    		if (isset($specific->tooladmin)) {return True;}
    		else {return False;}
    	}
    	if ($level == "PRIVACY") {
    		if (isset($specific->privacy)) {return True;}
    		else {return False;}
    	}
        if ($level == "ADMIN") {
            if (isset($specific->admin)) {return True;}
            else {return False;}
        }
    	if ($level == "USER") {
    		if (isset($specific->user)) {return True;}
    		else {return False;}
    	}
    }
    public static function checkCheckuser($id,$wiki) {
    	if(Permission::checkSecurity($id, "CHECKUSER",$wiki)) {
    		return True;
    	}
    	if(Permission::checkSecurity($id, "STEWARD","*")) {
    		return True;
    	}
    	if(Permission::checkSecurity($id, "STAFF","*")) {
    		return True;
    	}
    	if(Permission::checkSecurity($id, "DEVELOPER","*")) {
    		return True;
    	}
    	return False;
    }
    public static function checkOversight($id,$wiki) {
    	if(Permission::checkSecurity($id, "OVERSIGHT",$wiki)) {
            return True;
        }
        if(Permission::checkSecurity($id, "STEWARD","*")) {
            return True;
        }
        if(Permission::checkSecurity($id, "STAFF","*")) {
            return True;
        }
        if(Permission::checkSecurity($id, "DEVELOPER","*")) {
            return True;
        }
    	return False;
    }
    public static function checkPrivacy($id) {
        if(Permission::checkSecurity($id, "DEVELOPER","*")) {
            return True;
        }
    	if(Permission::checkSecurity($id, "STEWARD","*")) {
    		return True;
    	}
    	if(Permission::checkSecurity($id, "STAFF","*")) {
    		return True;
    	}
    	if(Permission::checkSecurity($id, "PRIVACY","*")) {
    		return True;
    	}
    	return False;
    }
    public static function checkAdmin($id,$wiki) {
        if(Permission::checkSecurity($id, "ADMIN",$wiki)) {
            return True;
        }
        if(Permission::checkSecurity($id, "DEVELOPER","*")) {
            return True;
        }
        if(Permission::checkSecurity($id, "STEWARD","*")) {
            return True;
        }
        if(Permission::checkSecurity($id, "STAFF","*")) {
            return True;
        }
        if(Permission::checkSecurity($id, "PRIVACY","*")) {
            return True;
        }
        return False;
    }
    public static function checkToolAdmin($id,$wiki) {
        if(Permission::checkSecurity($id, "TOOLADMIN",$wiki)) {
            return True;
        }
        if(Permission::checkSecurity($id, "DEVELOPER","*")) {
            return True;
        }
        if(Permission::checkSecurity($id, "STEWARD","*")) {
            return True;
        }
        if(Permission::checkSecurity($id, "STAFF","*")) {
            return True;
        }
        if(Permission::checkSecurity($id, "PRIVACY","*")) {
            return True;
        }
        if(Permission::checkSecurity($id, "OVERSIGHT",$wiki)) {
            return True;
        }
        if(Permission::checkSecurity($id, "CHECKUSER",$wiki)) {
            return True;
        }
        return False;
    }
}
