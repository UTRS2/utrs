<?php

namespace App\Http\Controllers\Admin;

use App\Services\Facades\MediaWikiRepository;
use App\Utils\Logging\RequestLogContext;
use DB;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyAdminAccount;
use Illuminate\Support\Str;
use App\Models\EmailBan;

/**
 * Controller for managing users.
 */
class UserController extends Controller
{
    /**
     * Displays user listing.
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);
        $allusers = User::paginate(50);

        $tableheaders = [
            __('admin.users.id'), 
            __('admin.users.name'), 
            __('admin.users.email'), 
            __('admin.users.perms'), 
            __('admin.users.ca-id')];
        $rowcontents = [];

        //if user is tooladmin, set $canAdmin to true
        $canAdmin = false;
        if (auth()->user()->can('update', User::class)) {
            $canAdmin = true;
        }

        return view('admin.users', ['title' => __('admin.users.title'), 'tableheaders' => $tableheaders, 'users' => $allusers, 'admin' => $canAdmin]);
    }

    /**
     * Display details of an individual user.
     * @param User $user user to display, auto-mapped from route params
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        // preload user objects for log entries; this reduces amount of DB queries
        $user->loadMissing('logs.user');

        // if user is not a developer, steward clerk, steward, WMF staff, or tooladmin, or sysop, then set $setemail to false
        $setemail = false;
        if ($user->hasAnySpecifiedPermsOnAnyWiki(['developer', 'stew_clerk', 'steward', 'staff', 'tooladmin', 'sysop'])) {
            $setemail = true;
        }

        // pull the env deepl languages, split into an array, and check if the input language is in the array
        $languages = env('DEEPL_LANGUAGE_CODES');
        $languages = explode(',', $languages);
        // convert the id of the language to the language code
        $langid = array_search($user->default_translation_language, $languages);

        return view('admin.users.view', ['user' => $user, 'setemail' => $setemail, 'verifiedemail' => false, 'languages' => $languages, 'langid' => $langid]);
    }

    // function for confirming the user's email
    public function confirmEmail(Request $request, User $user, $token)
    {
        // check if the token matches the user's email verified token
        if ($token == $user->email_verified_token) {
            // if the token matches, set the user's email verified boolean to true
            $user->email_verified = true;
            $user->saveOrFail();
            // redirect to the user's profile page
            return redirect()->route('admin.users.view', ['user' => $user, 'verifiedemail' => true]);
        } else {
            // if the token does not match, return an error
            return redirect()->route('admin.users.view', ['user' => $user])->withErrors(['email' => 'The token to verify the email does not match.']);
        }
    }

    /**
     * Save changes made to an individual user.
     * @param Request $request HTTP request details.
     * @param User $user user to display, auto-mapped from route params
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        DB::transaction(function () use ($request, $user) {
            $data = $request->validate([
                'reason' => 'required|string|min:3|max:128',
                'refresh_from_wiki' => 'nullable|in:0,1',
                'email' => 'email',
                'weekly_appeal_list' => 'in:0,1',
                'appeal_notifications' => 'in:0,1',
                'default_translation_language' => 'nullable|string',
            ]);

            //update the preferences without logging
            $user->weekly_appeal_list = $data['weekly_appeal_list'];
            $user->appeal_notifications = $data['appeal_notifications'];
            $user->saveOrFail();

            $reason = $data['reason'];

            /** @var User $currentUser */
            $currentUser = $request->user();

            $allChanges = [];

            if (isset($data['refresh_from_wiki']) && $data['refresh_from_wiki'] == 1) {
                $user->queuePermissionChecks();
                $allChanges[] = 'queue wiki permission reload';
            }

            // check if the request email matches the user's email in the database
            if ($user->email !== $request->input('email')) {
                //check if the input email is banned
                $emailban = EmailBan::where('email', $request->input('email'))->first();
                if ($emailban['accountbanned'] == 1) {
                    return redirect()->route('admin.users.view', ['user' => $user])->withErrors(['email' => 'The email is banned.']);
                }
                // if the user's email is not the same as the request email, set the user's email to the request email
                $user->email = $request->input('email');
                //generate a new email verified token using a random string of 32 characters
                $user->email_verified_token = Str::random(32);
                $user->saveOrFail();
                Mail::to($user->email)->send(new VerifyAdminAccount($user->email, route('admin.users.confirmemail', ['user' => $user->id, 'token' => $user->email_verified_token]), $user->username));
                $allChanges[] = 'email: ' . $user->email;
            }

            foreach (MediaWikiRepository::getSupportedTargets() as $wiki) {
                /** @var Permission $permission */
                $permission = $user->permissions->where('wiki', $wiki)->first();

                $updateSet = [];


                /** @var Permission $permission */
                foreach (Permission::ALL_POSSIBILITIES as $key) {
                    $oldValue = $permission && $permission->$key;

                    if ($currentUser->can('updatePermission', [$user, $wiki, $key])) {
                        $value = (bool) $request->input('permission.' . $wiki . '.' . $key, false);

                        if ($value != $oldValue) {
                            $updateSet[$key] = $value;
                        }
                    }
                }

                if (!empty($updateSet)) {
                    if (!$permission) {
                        $permission = new Permission();
                        $permission->user_id = $user->id;
                        $permission->wiki = $wiki;
                    }

                    $permission->fill($updateSet)->saveOrFail();
                    $updateDetails = [];

                    foreach ($updateSet as $key => $value) {
                        $updateDetails[] = ($value ? '+' : '-') . $key;
                    }

                    $allChanges[] = $wiki . ': ' .implode(', ', $updateDetails);
                }
            }

            // pull the env deepl languages, split into an array, and check if the input language is in the array
            $languages = env('DEEPL_LANGUAGE_CODES');
            $languages = explode(',', $languages);
            // convert the id of the language to the language code
            $langname = $languages[$request->input('default_translation_language')];

            // update the user's default_translation_language if changed and log
            if ($user->default_translation_language !== $request->input('default_translation_language')) {
                if (!in_array($langname, $languages)) {
                    return redirect()->route('admin.users.view', ['user' => $user])->withErrors(['default_translation_language' => 'The default translation language is not valid.']);
                }
                $allChanges[] = 'default translation language: ' . $langname;
                $user->default_translation_language = $langname;
                $user->saveOrFail();
            }

            if (!empty($allChanges)) {
                $user->addLog(
                    new RequestLogContext($request),
                    'modified user - ' . implode(', ', $allChanges),
                    $reason
                );
            }
        });

        return redirect()->route('admin.users.view', [ 'user' => $user ]);
    }
}
