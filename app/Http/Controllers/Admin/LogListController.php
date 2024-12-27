<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LogEntry;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LogListController extends Controller
{
    public function index() {
        // check if the user is a developer, if not, throw a 403
        $user = Auth::user();
        $isDeveloper = $user->hasAnySpecifiedLocalOrGlobalPerms([], 'developer');
        if (!$isDeveloper) {
            return abort(403);
        }
        $logs = LogEntry::orderBy('id','desc')->paginate(100);

        return view('admin.logs', [
            'logs' => $logs,
            'users' => \App\Models\User::all(),
        ]);
    }
}
