<?php

namespace App\Http\Controllers\Admin;

use App\Services\Facades\MediaWikiRepository;
use App\Utils\Logging\RequestLogContext;
use DB;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
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

        $tableheaders = ['ID', 'Username', 'CentralAuth ID', 'Last Permissions Check'];
        $rowcontents = [];

        foreach ($allusers as $user) {
            $allperms = Permission::where('user_id',$user->id)->get();
            $activePerms = sizeof($allperms) > 0;
            if(!$activePerms) {continue;}
            $canAdmin = false;
            foreach($allperms as $perm) {
                if(
                    $perm->oversight ||
                    $perm->checkuser ||
                    $perm->steward ||
                    $perm->staff ||
                    $perm->developer ||
                    $perm->tooladmin ||
                    $perm->admin
                ) {
                    $canAdmin = true;
                }
            }
            if (!$canAdmin) {continue;}
            $idbutton = '<a href="' . route('admin.users.view', $user) . '"><button type="button" class="btn btn-primary">' . $user->id . '</button></a>';
            $rowcontents[$user->id] = [$idbutton, htmlspecialchars($user->username), $user->mediawiki_id ?? '(not known)', $user->last_permission_check_at];
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
        $user->loadMissing('logs.user');

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
