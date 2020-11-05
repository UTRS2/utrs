<?php

namespace App\Http\Controllers;

use App\Models\Appeal;
use App\Models\Ban;
use App\Models\LogEntry;
use App\Models\Sitenotice;
use App\Models\Template;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
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

        $tableheaders = ['ID', 'Name', 'Contents', 'Active'];
        $rowcontents = [];

        foreach ($alltemplates as $template) {
            $idbutton = '<a href="/admin/templates/' . $template->id . '"><button type="button" class="btn btn-primary">' . $template->id . '</button></a>';
            $active = $template->active ? 'Yes' : 'No';
            $rowcontents[$template->id] = [$idbutton, $template->name, htmlspecialchars($template->template), $active];
        }

        return view('admin.tables', ['title' => 'All Templates', 'tableheaders' => $tableheaders, 'rowcontents' => $rowcontents, 'new' => true, 'createlink' => '/admin/templates/create', 'createtext' => 'New template']);
    }

    public function showNewTemplate()
    {
        $this->authorize('create', Template::class);
        return view('admin.newtemplate');
    }

    public function makeTemplate(Request $request)
    {
        $this->authorize('create', Template::class);

        $ua = $request->userAgent();
        $ip = $request->ip();
        $lang = $request->header('Accept-Language');

        $data = $request->validate([
            'name' => ['required', 'min:2', 'max:128', Rule::unique('templates', 'name')],
            'template' => 'required|min:2|max:2048',
            'default_status' => ['required', Rule::in(Appeal::REPLY_STATUS_CHANGE_OPTIONS)],
        ]);

        $data['active'] = 1;

        $template = Template::create($data);

        LogEntry::create(array('user_id' => Auth::id(), 'model_id' => $template->id, 'model_type' => Template::class, 'action' => 'create', 'ip' => $ip, 'ua' => $ua . " " . $lang));
        return redirect()->to('/admin/templates');
    }

    public function editTemplate(Template $template)
    {
        $this->authorize('update', $template);
        return view('admin.edittemplate', ["template" => $template]);
    }

    public function updateTemplate(Request $request, Template $template)
    {
        $this->authorize('update', $template);

        $ua = $request->userAgent();
        $ip = $request->ip();
        $lang = $request->header('Accept-Language');

        $data = $request->validate([
            'name' => ['required', 'min:2', 'max:128', Rule::unique('templates', 'name')->ignore($template->id)],
            'template' => 'required|min:2|max:2048',
            'default_status' => ['required', Rule::in(Appeal::REPLY_STATUS_CHANGE_OPTIONS)],
        ]);

        $template->update($data);
        LogEntry::create(array('user_id' => Auth::id(), 'model_id' => $template->id, 'model_type' => Template::class, 'action' => 'update', 'ip' => $ip, 'ua' => $ua . " " . $lang));
        return redirect()->to('/admin/templates');
    }
}
