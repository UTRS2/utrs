<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailPreviewController extends Controller
{
    public function preview() {
        // if user is not a developer, return 403
        abort_unless(Auth::check(), 403, 'No logged in user');
        abort_unless(Auth::user()->hasAnySpecifiedLocalOrGlobalPerms([], 'developer'), 403, 'You are not a developer');

        // get all possible emails from the emails directory
        $emails = array_diff(scandir(resource_path('views/emails')), array('..', '.'));
        //go through each email and remove the .blade.php extension
        foreach ($emails as $key => $email) {
            $emails[$key] = str_replace('.blade.php', '', $email);
        }
        return view('admin.emailspreview', ['emails' => $emails]);
    }

    public function previewEmail($email) {
        // if user is not a developer, return 403
        abort_unless(Auth::check(), 403, 'No logged in user');
        abort_unless(Auth::user()->hasAnySpecifiedLocalOrGlobalPerms([], 'developer'), 403, 'You are not a developer');

        // if the email is:
            // - not in the emails directory, return a 404
            // - email is acc, then add a parameter to the view for a fake acc url
            // - email is appealkey, then add a parameter to the view for a fake appeal key
        if (!file_exists(resource_path('views/emails/' . $email . '.blade.php'))) {
            abort(404);
        }
        $stopUrl = 'https://utrs.fake/stopurl';
        if ($email == 'acc') {
            return view('emails.' . $email, ['url' => 'https://acc.fake/specialurl', 'stopUrl' => $stopUrl]);
        }
        if ($email == 'appealkey') {
            return view('emails.' . $email, ['appealkey' => '123456789123456789123456789123456789123456789123456789', 'stopUrl' => $stopUrl]);
        }
        return view('emails.' . $email, );
    }
}
