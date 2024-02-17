<?php

namespace App\Http\Controllers\Admin;

use App\Models\LogEntry;
use App\Models\User;
use App\Models\EmailBan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Bans\CreateBanRequest;
use App\Http\Requests\Admin\Bans\UpdateBanRequest;
use App\Policies\Admin\BanPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class EmailBanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $banlist = EmailBan::paginate(50);

        return view('admin.emailbans', [
            'emailBans' => $banlist,
        ]);

    }

    public function appeal(Request $request, $id) {
        $reason = $request->input('reason');
        // if reason is blank or less than 5 characters, return to the form with an error
        if (strlen($reason) < 5) {
            return redirect()->back()->with('error', 'Reason must be at least 5 characters');
        }
        $user = Auth::user();
        $ban = EmailBan::findOrFail($id);
        //check the status of appeal ban, and then reverse it and log it
        if ($ban->appealbanned == 0) {
            $ban->appealbanned = 1;
            $ban->save();
            $ban->logEmailBan($user, $ban, ' has been prohibited from appealing', $reason);
            // return to the email ban list with a success message
            return redirect()->route('admin.emailban.list')->with('success', 'Email Ban Appeal Status Updated');
        } else {
            $ban->appealbanned = 0;
            $ban->save();
            $ban->logEmailBan($user, $ban, ' has been allowed to appeal again', $reason);
            // return to the email ban list with a success message
            return redirect()->route('admin.emailban.list')->with('success', 'Email Ban Appeal Status Updated');
        }
    }

    public function account(Request $request, $id) {
        $reason = $request->input('reason');
        // if reason is blank or less than 5 characters, return to the form with an error
        if (strlen($reason) < 5) {
            return redirect()->back()->with('error', 'Reason must be at least 5 characters');
        }
        $ban = EmailBan::findOrFail($id);
        $user = Auth::user();
        //check the status of emailban account field, and then reverse it and log it
        if ($ban->accountbanned == 0) {
            $ban->accountbanned = 1;
            $ban->save();
            $ban->logEmailBan($user, $ban, ' has been prohibited from adding this email to an account', $reason);
            // return to the email ban list with a success message
            return redirect()->route('admin.emailban.list')->with('success', 'Email Ban Account Status Updated');
        } else {
            $ban->accountbanned = 0;
            $ban->save();
            $ban->logEmailBan($user, $ban, ' has been allowed to add this email to an account again', $reason);
            // return to the email ban list with a success message
            return redirect()->route('admin.emailban.list')->with('success', 'Email Ban Account Status Updated');
        }
    }

    public function appealreason($ban) {
        $type = 'appeal';
        $ban = EmailBan::findOrFail($ban);

        return view('admin.emailbanreason', [
            'emailBan' => $ban,
            'type' => $type,

        ]);
    }

    public function accountreason($ban) {
        $type = 'account';
        $ban = EmailBan::findOrFail($ban);

        return view('admin.emailbanreason', [
            'emailBan' => $ban,
            'type' => $type,
        ]);
    }
}
