<?php

namespace App\Http\Controllers\Appeal;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\EmailBan;

class PublicEmailBanController extends Controller
{
    public function showForm($method, $token) {
        // if the method is not account or appeal, error 404
        if ($method !== 'account' && $method !== 'appeal') {
            abort(404);
        }

        // check the email bans table for the token
        // if the token is found, return the view with the token
        // if the token is not found, error 403
        if (EmailBan::where('uid', $token)->exists()) {
            // if the email contains @wiki, set a variable to true indicating it's an account ban
            $email = EmailBan::where('uid', $token)->first()->email;
            if ($method == 'account') {
                // check if the email contains @wiki, and if not, display 404 error
                if (strpos($email, '@wiki') === false) {
                    abort(404, __('emails.ban.not-account'));
                }

                // remove the @wiki from the email
                $email = str_replace('@wiki', '', $email);
            }
            return view('appeals.public.emailban', ['token' => $token, 'isAccountBan' => $method == "account" ? True:False, 'name' => $email, 'method' => $method]);
        } else {
            abort(403);
        }
    }

    public function submit(Request $request, $method, $token) {
        // if neither account nor appeal, return to the form with an error
        if (!$request->input('account') && !$request->input('appeal')){
            return redirect()->back()->with('error', __('emails.ban.invalid-method'));
        }

        // check the email bans table for the token
        // if the token is not found, error 403
        if (EmailBan::where('uid', $token)->exists()) {
            // set appealbanned and accountbanned based on the request
            $appealBanned = $request->input('appeal') ? 1 : 0;
            $accountBanned = $request->input('account') ? 1 : 0;

            // update the email bans table with the new values
            EmailBan::where('uid', $token)->update(['appealbanned' => $appealBanned, 'accountbanned' => $accountBanned]);

            // return to the form with a success message
            return redirect()->back()->with('success', __('emails.ban.success'));
        } else {
            abort(403);
        }
    }
}
