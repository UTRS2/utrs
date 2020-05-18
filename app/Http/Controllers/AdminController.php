<?php

namespace App\Http\Controllers;

use App\Ban;
use App\Log;
use App\Permission;
use App\Sitenotice;
use App\Template;
use App\User;
use App\Wikitask;
use Auth;
use Illuminate\Http\Request;
use Redirect;

class AdminController extends Controller
{
    public function listusers()
    {
        $allusers = User::all();
        $currentuser = User::findOrFail(Auth::id());
        $permission = false;
        $wikilist = explode(",", $currentuser->wikis);
        foreach ($wikilist as $wiki) {
            if (Permission::checkToolAdmin(Auth::id(), $wiki)) {
                $permission = true;
            }
        }
        abort_unless($permission, 403, 'Forbidden');

        $tableheaders = ['ID', 'Username', 'Verified', 'Wikis'];
        $rowcontents = [];
        foreach ($allusers as $user) {
            $idbutton = '<a href="/admin/users/' . $user->id . '"><button type="button" class="btn btn-primary">' . $user->id . '</button></a>';
            $verified = $user->verified ? 'Yes' : 'No';
            $rowcontents[$user->id] = [$idbutton, htmlspecialchars($user->username), $verified, $user->wikis];
        }

        return view('admin.tables', ['title' => 'All Users', 'tableheaders' => $tableheaders, 'rowcontents' => $rowcontents]);
    }

    public function listbans()
    {
        $allbans = Ban::all();
        $currentuser = User::findOrFail(Auth::id());

        $canSeeProtectedBans = false;
        $permission = false;

        $wikilist = explode(",", $currentuser->wikis);

        foreach ($wikilist as $wiki) {
            if (!$permission && Permission::checkToolAdmin(Auth::id(), $wiki)) {
                $permission = true;
            }

            if (!$canSeeProtectedBans && Permission::checkPrivacy(Auth::id(), $wiki)) {
                $canSeeProtectedBans = true;
            }
        }

        if (!$permission) {
            abort(403);
        }

        $tableheaders = ['ID', 'Target', 'Expires', 'Reason'];
        $rowcontents = [];

        foreach ($allbans as $ban) {
            $idbutton = '<a href="/admin/bans/' . $ban->id . '"><button type="button" class="btn '.($ban->is_protected ? 'btn-danger' : 'btn-primary').'">' . $ban->id . '</button></a>';
            $targetName = htmlspecialchars($ban->target);

            if ($ban->is_protected) {
                $targetName = $canSeeProtectedBans ? '<i class="text-danger">' . $targetName . '</i>'
                    : '<i class="text-muted">(ban target removed)</i>';
            }

            $rowcontents[$ban->id] = [$idbutton, $targetName, $ban->expiry, htmlspecialchars($ban->reason)];
        }

        if ($canSeeProtectedBans) {
            $caption = "Any ban showing in red has been oversighted and should not be shared to others who do not have access to it.";
        } else {
            $canSeeProtectedBans = null;
        }

        return view('admin.tables', ['title' => 'All Bans', 'tableheaders' => $tableheaders, 'rowcontents' => $rowcontents, 'caption' => $caption]);
    }

    public function listsitenotices()
    {
        $allsitenotice = Sitenotice::all();
        $currentuser = User::findOrFail(Auth::id());
        $permission = false;
        $wikilist = explode(",", $currentuser->wikis);
        foreach ($wikilist as $wiki) {
            if (Permission::checkToolAdmin(Auth::id(), $wiki)) {
                $permission = true;
            }
        }

        abort_unless($permission, 403, 'Forbidden');

        $tableheaders = ['ID', 'Message'];
        $rowcontents = [];
        foreach ($allsitenotice as $sitenotice) {
            $idbutton = '<a href="/admin/sitenotices/' . $sitenotice->id . '"><button type="button" class="btn btn-primary">' . $sitenotice->id . '</button></a>';
            $rowcontents[$sitenotice->id] = [$idbutton, $sitenotice->message];
        }
        return view('admin.tables', ['title' => 'All Sitenotices', 'tableheaders' => $tableheaders, 'rowcontents' => $rowcontents]);
    }

    public function listtemplates()
    {
        $alltemplates = Template::all();
        $currentuser = User::findOrFail(Auth::id());
        $permission = false;
        $wikilist = explode(",", $currentuser->wikis);
        foreach ($wikilist as $wiki) {
            if (Permission::checkToolAdmin(Auth::id(), $wiki)) {
                $permission = true;
            }
        }

        abort_unless($permission, 403, 'Forbidden');

        $tableheaders = ['ID', 'Target', 'Expires', 'Active'];
        $rowcontents = [];
        foreach ($alltemplates as $template) {
            $idbutton = '<a href="/admin/templates/' . $template->id . '"><button type="button" class="btn btn-primary">' . $template->id . '</button></a>';
            $active = $template->active ? 'Yes' : 'No';
            $rowcontents[$template->id] = [$idbutton, $template->name, htmlspecialchars($template->template), $active];
        }
        return view('admin.tables', ['title' => 'All Templates', 'tableheaders' => $tableheaders, 'rowcontents' => $rowcontents, 'new' => true]);
    }

    public function verifyAccount()
    {
        if (Auth::user()->verified) {
            return Redirect::to('/home');
        } else {
            Wikitask::create(['task' => 'verifyaccount', 'actionid' => Auth::id()]);
            return view('admin.verifyme');
        }
    }

    public function verify($code)
    {
        $user = User::where('u_v_token', '=', $code)->first();
        $user->verified = 1;
        $user->save();
        return redirect()->to('/home');
    }

    public function makeTemplate(Request $request)
    {
        if (!Permission::checkToolAdmin(Auth::id(), "*")) {
            abort(401);
        }
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();

        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $newtemplate = $request->all();
        $name = $newtemplate['name'];
        $template = $newtemplate['template'];
        $creation = Template::create(['name' => $name, 'template' => $template, 'active' => 1]);
        $log = Log::create(array('user' => Auth::id(), 'referenceobject' => $creation->id, 'objecttype' => 'template', 'action' => 'create', 'ip' => $ip, 'ua' => $ua . " " . $lang));
        return Redirect::to('/admin/templates');
    }

    public function saveTemplate(Request $request, $id)
    {
        if (!Permission::checkToolAdmin(Auth::id(), "*")) {
            abort(401);
        }
        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');
        $data = $request->all();
        $template = Template::findOrFail($id);
        $template->name = $data['name'];
        $template->template = $data['template'];
        $template->save();
        $log = Log::create(array('user' => Auth::id(), 'referenceobject' => $template->id, 'objecttype' => 'template', 'action' => 'update', 'ip' => $ip, 'ua' => $ua . " " . $lang));
        return Redirect::to('/admin/templates');
    }

    public function showNewTemplate()
    {
        return view('admin.newtemplate');
    }

    public function modifyTemplate($id)
    {
        $template = Template::findOrFail($id);
        return view('admin.edittemplate', ["template" => $template]);
    }
}
