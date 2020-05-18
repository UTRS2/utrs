<?php

namespace App\Http\Controllers\Admin;

use DB;
use App\Log;
use App\Permission;
use Illuminate\Support\Arr;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', User::class);
        $allusers = User::all();

        $tableheaders = ['ID', 'Username', 'Verified', 'Wikis'];
        $rowcontents = [];

        foreach ($allusers as $user) {
            $idbutton = '<a href="/admin/users/' . $user->id . '"><button type="button" class="btn btn-primary">' . $user->id . '</button></a>';
            $verified = $user->verified ? 'Yes' : 'No';
            $rowcontents[$user->id] = [$idbutton, htmlspecialchars($user->username), $verified, $user->wikis];
        }

        return view('admin.tables', ['title' => 'All Users', 'tableheaders' => $tableheaders, 'rowcontents' => $rowcontents]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        return view('admin.users.view', ['user' => $user]);
    }

    public function edit(User $user)
    {
        //
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        DB::transaction(function () use ($request, $user) {
            $reason = $request->validate([
                'reason' => 'required|string|min:3|max:128',
            ])['reason'];

            /** @var User $currentUser */
            $currentUser = $request->user();

            $allChanges = [];

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
                    $allChanges[$permission->wiki] = $updateSet;
                }
            }

            $ua = $request->header('User-Agent');
            $ip = $request->ip();
            $lang = $request->header('Accept-Language');

            Log::create([
                'user' => $currentUser->id,
                'referenceobject' => $user->id,
                'objecttype' => User::class,
                'action' => 'change permissions - ' . json_encode($allChanges),
                'reason' => $reason,
                'ip' => $ip,
                'ua' => $ua . " " . $lang,
                'protected' => 0
            ]);
        });

        return redirect()->back();
    }

    public function destroy(User $user)
    {
        //
    }
}
