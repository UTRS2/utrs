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
    	if ($level == "OVERSIGHT") {
    		if ($specific->oversight==1) {return True;}
    		else {return False;}
    	}
    	if ($level == "CHECKUSER") {
    		if ($specific->checkuser==1) {return True;}
    		else {return False;}
    	}
    	if ($level == "STEWARD") {
    		if ($specific->steward==1) {return True;}
    		else {return False;}
    	}
    	if ($level == "STAFF") {
    		if ($specific->staff==1) {return True;}
    		else {return False;}
    	}
    	if ($level == "DEVELOPER") {
    		if ($specific->developer==1) {return True;}
    		else {return False;}
    	}
    	if ($level == "TOOLADMIN") {
    		if ($specific->tooladmin==1) {return True;}
    		else {return False;}
    	}
    	if ($level == "PRIVACY") {
    		if ($specific->privacy==1) {return True;}
    		else {return False;}
    	}
        if ($level == "ADMIN") {
            if ($specific->admin==1) {return True;}
            else {return False;}
        }
    	if ($level == "USER") {
    		if ($specific->user==1) {return True;}
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
