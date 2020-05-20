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
    public function listbans(Request $request)
    {
        $this->authorize('viewAny', Ban::class);
        $allbans = Ban::all();

        /** @var User $user */
        $user = $request->user();

        $canSeeProtectedBans = false;

        $tableheaders = ['ID', 'Target', 'Expires', 'Reason'];
        $rowcontents = [];

        foreach ($allbans as $ban) {
            $idbutton = '<a href="/admin/bans/' . $ban->id . '"><button type="button" class="btn '.($ban->is_protected ? 'btn-danger' : 'btn-primary').'">' . $ban->id . '</button></a>';
            $targetName = htmlspecialchars($ban->target);

            if ($ban->is_protected) {
                $canSee = $user->can('viewName', $ban);

                if (!$canSeeProtectedBans && $canSee) {
                    $canSeeProtectedBans = true;
                }

                $targetName = $canSee ? '<i class="text-danger">' . $targetName . '</i>'
                    : '<i class="text-muted">(ban target removed)</i>';
            }

            $rowcontents[$ban->id] = [$idbutton, $targetName, $ban->expiry, htmlspecialchars($ban->reason)];
        }

        if ($canSeeProtectedBans) {
            $caption = "Any ban showing in red has been oversighted and should not be shared to others who do not have access to it.";
        } else {
            $caption = null;
        }

        return view('admin.tables', ['title' => 'All Bans', 'tableheaders' => $tableheaders, 'rowcontents' => $rowcontents, 'caption' => $caption]);
    }

    public function listsitenotices()
    {
        $this->authorize('viewAny', Sitenotice::class);
        $allsitenotice = Sitenotice::all();

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
        $this->authorize('viewAny', Template::class);
        $alltemplates = Template::all();

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
        if (!Auth::check() || Auth::user()->verified) {
            return redirect('/');
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
        $this->authorize('create', Template::class);

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

    public function saveTemplate(Request $request, Template $template)
    {
        $this->authorize('update', $template);

        $ua = $request->server('HTTP_USER_AGENT');
        $ip = $request->ip();
        $lang = $request->server('HTTP_ACCEPT_LANGUAGE');

        $template->name = $request->input('name');
        $template->template = $request->input('template');
        $template->save();

        Log::create(array('user' => Auth::id(), 'referenceobject' => $template->id, 'objecttype' => 'template', 'action' => 'update', 'ip' => $ip, 'ua' => $ua . " " . $lang));
        return redirect()->to('/admin/templates');
    }

    public function showNewTemplate()
    {
        $this->authorize('create', Template::class);
        return view('admin.newtemplate');
    }

    public function modifyTemplate(Template $template)
    {
        $this->authorize('update', $template);
        return view('admin.edittemplate', ["template" => $template]);
    }
}
