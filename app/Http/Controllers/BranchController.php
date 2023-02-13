<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Exception;

class BranchController extends Controller
{
    public function getBranches() {
        exec('git fetch --prune --all');
        $raw_branches = explode("\n",shell_exec('git branch -l'));
        $branches=array();
        foreach ($raw_branches as $branch) {
            if ($branch == "" || str_contains($branch, 'HEAD')) {
                continue;
            }
            $branches[] = str_replace("  ", "", $branch); 
        }
        return $branches;
    }
    public function switchBranch($branch) {
        $user = Auth::user();
        abort_if(!$user,403,'You are not logged in');
        try {
            $test = !$user->can('changebranch');
        } catch (Exception $e) {
            abort(403,'You are not a developer');
        }

        abort_if(env('APP_ENV')=="production", 403, 'Branch changes are not permitted in production');

        $branches=$this->getBranches();
        
        if (!in_array($branch, $branches)) {
            throw new Exception('Branch does not exist or is the current branch');
        }
        else {
            shell_exec('git checkout '.$branch);
            return redirect('/');
        }
    }

    public function showBranchList()
    {
        $user = Auth::user();
        abort_if(!$user,403,'You are not logged in');
        try {
            $test = !$user->can('changebranch');
        } catch (Exception $e) {
            abort(403,'You are not a developer');
        }

        abort_if(env('APP_ENV')=="production", 403, 'Branch changes are not permitted in production');
        
        $branches=$this->getBranches();
        return $branches;
        //return view('branches');
    }
}
