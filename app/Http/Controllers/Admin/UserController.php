<?php

namespace App\Http\Controllers\Admin;

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
        dd($request->all());
    }

    public function destroy(User $user)
    {
        //
    }
}
