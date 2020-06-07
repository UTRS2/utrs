<?php

namespace App\Http\Controllers\Admin;

use DB;
use App\Log;
use App\Permission;
use Illuminate\Support\Arr;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

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
        $allusers = User::all();

        $tableheaders = ['ID', 'Username', 'Verified', 'Wikis'];
        $rowcontents = [];

        foreach ($allusers as $user) {
            $idbutton = '<a href="' . route('admin.users.view', $user) . '"><button type="button" class="btn btn-primary">' . $user->id . '</button></a>';
            $verified = $user->verified ? 'Yes' : 'No';
            $rowcontents[$user->id] = [$idbutton, htmlspecialchars($user->username), $verified, $user->wikis];
        }

        return view('admin.tables', ['title' => 'All Users', 'tableheaders' => $tableheaders, 'rowcontents' => $rowcontents]);
    }

    /**
     * Display details of an individual user.
     * @param User $user user to display, auto-mapped from route params
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        // preload user objects for log entries; this reduces amount of DB queries
        $user->loadMissing('logs.userObject');

        return view('admin.users.view', ['user' => $user]);
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
            ]);

            $reason = $data['reason'];

            /** @var User $currentUser */
            $currentUser = $request->user();

            $allChanges = [];

            if (isset($data['refresh_from_wiki']) && $data['refresh_from_wiki'] == 1) {
                $user->queuePermissionChecks();
                $allChanges[] = 'queue wiki permission reload';
            }

            foreach ($user->permissions as $permission) {
                $updateSet = [];

                /** @var Permission $permission */
                foreach (Permission::ALL_POSSIBILITIES as $key) {
                    if ($currentUser->can('updatePermission', [$user, $permission->wiki, $key])) {
                        $value = (bool) $request->input('permission.' . $permission->wikiFormKey . '.' . $key, false);

                        if ($value != $permission->$key) {
                            $updateSet[$key] = $value;
                        }
                    }
                }

                if (!empty($updateSet)) {
                    $permission->update($updateSet);
                    $updateDetails = [];

                    foreach ($updateSet as $key => $value) {
                        $updateDetails[] = ($value ? '+' : '-') . $key;
                    }

                    $allChanges[] = $permission->wiki . ': ' .implode(', ', $updateDetails);
                }
            }

            if (!empty($allChanges)) {
                $ua = $request->header('User-Agent');
                $ip = $request->ip();
                $lang = $request->header('Accept-Language');

                Log::create([
                    'user' => $currentUser->id,
                    'referenceobject' => $user->id,
                    'objecttype' => User::class,
                    'action' => 'modified user - ' . implode(',', $allChanges),
                    'reason' => $reason,
                    'ip' => $ip,
                    'ua' => $ua . " " . $lang,
                    'protected' => 0
                ]);
            }
        });

        return redirect()->back();
    }
}
