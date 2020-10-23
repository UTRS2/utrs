<?php

namespace App\Http\Controllers\Admin;

use App\Services\Facades\MediaWikiRepository;
use DB;
use App\Log;
use App\Permission;
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

        $tableheaders = ['ID', 'Username', 'Last Permissions Check'];
        $rowcontents = [];

        foreach ($allusers as $user) {
            $idbutton = '<a href="' . route('admin.users.view', $user) . '"><button type="button" class="btn btn-primary">' . $user->id . '</button></a>';
            $rowcontents[$user->id] = [$idbutton, htmlspecialchars($user->username), $user->last_permission_check_at];
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

            foreach (MediaWikiRepository::getSupportedTargets() as $wiki) {
                $wikiDbName = $wiki === 'global' ? '*' : $wiki;
                /** @var Permission $permission */
                $permission = $user->permissions->where('wiki', $wikiDbName)->first();

                $updateSet = [];

                /** @var Permission $permission */
                foreach (Permission::ALL_POSSIBILITIES as $key) {
                    $oldValue = $permission && $permission->$key;

                    if ($currentUser->can('updatePermission', [$user, $wikiDbName, $key])) {
                        $value = (bool) $request->input('permission.' . $wiki . '.' . $key, false);

                        if ($value != $oldValue) {
                            $updateSet[$key] = $value;
                        }
                    }
                }

                if (!empty($updateSet)) {
                    if (!$permission) {
                        $permission = new Permission();
                        $permission->userid = $user->id;
                        $permission->wiki = $wikiDbName;
                    }

                    $permission->fill($updateSet)->saveOrFail();
                    $updateDetails = [];

                    foreach ($updateSet as $key => $value) {
                        $updateDetails[] = ($value ? '+' : '-') . $key;
                    }

                    $allChanges[] = $wiki . ': ' .implode(', ', $updateDetails);
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
                    'protected' => Log::LOG_PROTECTION_NONE,
                ]);
            }
        });

        return redirect()->route('admin.users.view', [ 'user' => $user ]);
    }
}
