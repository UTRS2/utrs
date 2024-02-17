<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\AppealKey;
use App\Models\Appeal;
use App\Models\EmailBan;
use App\Services\Facades\MediaWikiRepository;
use Illuminate\Support\Facades\Mail;

class AppealKeyController extends Controller
{
    public function sendAppealKeyReminder(Request $request) {
        $email = $request->input('email');
        if ($email == null) {
            return redirect()->back()->with('error', 'No email was provided');
        }
        $ban = EmailBan::where('email', $email)->first();

        $appeal = Appeal::where('email',$email)->orderBy('id','desc')->first();

        // check if the user can be e-mailed according to MediaWiki API
        if ($appeal->user_verified == -1 || $appeal->blocktype == 0) {
            if ($appeal->email != '' && $appeal->email != null && strpos($appeal->email, ':') !== true) {
                // check if the email is on the prohibited email domains list in the env
                if (in_array(explode('@', $appeal->email)[1], explode(',', env('PROHIBITED_EMAIL_DOMAINS')))) {
                    // return back to the original page with an error message
                    return redirect()->back()->with('error', 'Using this email domain is prohibited');
                }
                // check if the email has had the last email sent within the last 24 hours
                // using the lastemail field in the database
                // if it has, act as if the email was sent, but only provide a generic message that if there was an email attached to the appeal, it would be sent
                // also check if the field is null, if it is, continue
                if ($ban->lastemail != null) {
                    if (strtotime($ban->lastemail) > strtotime('-24 hours')) {
                        return redirect()->back()->with('success', 'If there was an email attached to this appeal, and there were no internal errors, it would have been sent.');
                    }
                }

                $lang = $appeal->getWikiDefaultLanguage();
                // if lang is en-us, change it to en
                if ($lang == 'en-us') {
                    $lang = 'en';
                }

                // send email using mailcoach
                Mail::to($appeal->email)->locale($lang)->send(new AppealKey($appeal->appealsecretkey, $appeal->email));
                // set the lastemail field to the current time
                $ban->lastemail = date('Y-m-d H:i:s');
                $ban->save();
                
                return redirect()->back()->with('success', 'If there was an email attached to this appeal, and there were no internal errors, it would have been sent.');
            }
        } else {
            // check if there is an email entry under the username using the format "{username}@wiki"
            $appeal->getWikiEmailUsername();
            $ban = EmailBan::where('email', $appeal->getWikiEmailUsername().'@wiki')->first();
            // check if the email is accountbanned
            if ($ban->accountbanned == 1) {
                return redirect()->back()->with('error', 'This account is banned from using this function');
            }
            // if lastemail is not null, check if it was sent within the last 24 hours and if it was, return to the original page with a success message
            if ($ban->lastemail != null) {
                if (strtotime($ban->lastemail) > strtotime('-24 hours')) {
                    return redirect()->back()->with('success', 'If there was an email attached to this appeal, and there were no internal errors, it would have been sent.');
                }
            }
            $result = MediaWikiRepository::getApiForTarget($appeal->wiki)->getMediaWikiExtras()->sendEmail($appeal->getWikiEmailUsername(), $title, $message);
            return redirect()->back()->with('success', 'If there was an email attached to this appeal, and there were no internal errors, it would have been sent.');

        }
    }
}
