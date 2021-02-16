<?php

namespace App\Http\Controllers;

use App\Models\Wiki;
use App\Models\Appeal;
use App\Models\Sitenotice;
use App\Models\Template;
use App\Models\User;
use App\Utils\Logging\RequestLogContext;
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

    public function listtemplates(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $wikis = Wiki::get()
            ->filter(function (Wiki $wiki) use ($user) {
                return $user->can('viewAny', [Template::class, $wiki]);
            })
            ->pluck('id');

        if ($wikis->isEmpty()) {
            abort(403, "You can't view templates in any wikis!");
            return '';
        }

        $templates = Template::with('wiki')
            ->whereIn('wiki_id', $wikis)
            ->get();

        $tableheaders = ['ID', 'Name', 'Contents', 'Active'];
        if ($wikis->count() > 1) {
            $tableheaders[] = 'Wiki';
        }

        $rowcontents = [];

        foreach ($templates as $template) {
            $idbutton = '<a href="/admin/templates/' . $template->id . '" class="btn btn-primary">' . $template->id . '</a>';
            $active = $template->active ? 'Yes' : 'No';

            $rowcontents[$template->id] = [$idbutton, $template->name, htmlspecialchars($template->template), $active];

            if ($wikis->count() > 1) {
                $wikiName = $template->wiki->display_name . ' (' . $template->wiki->database_name . ')';
                $rowcontents[$template->id][] = $wikiName;
            }
        }

        return view('admin.tables', ['title' => 'All Templates', 'tableheaders' => $tableheaders, 'rowcontents' => $rowcontents, 'new' => true, 'createlink' => '/admin/templates/create', 'createtext' => 'New template']);
    }

    public function showNewTemplate(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $wikis = Wiki::get()
            ->filter(function (Wiki $wiki) use ($user) {
                return $user->can('create', [Template::class, $wiki]);
            })
            ->mapWithKeys(function (Wiki $wiki) {
                return [$wiki->id => $wiki->display_name . ' (' . $wiki->database_name . ')'];
            });

        if ($wikis->isEmpty()) {
            abort(403, "You can't create templates in any wikis!");
            return '';
        }

        return view('admin.newtemplate', ['wikis' => $wikis]);
    }

    public function makeTemplate(Request $request)
    {
        $ua = $request->userAgent();
        $ip = $request->ip();
        $lang = $request->header('Accept-Language');

        $data = $request->validate([
            'name' => ['required', 'min:2', 'max:128', Rule::unique('templates', 'name')],
            'template' => 'required|min:2|max:2048',
            'default_status' => ['required', Rule::in(Appeal::REPLY_STATUS_CHANGE_OPTIONS)],
            'wiki_id' => 'required|exists:wikis,id',
        ]);

        $this->authorize('create', [Template::class, Wiki::findOrFail($data['wiki_id'])]);

        $data['active'] = 1;

        $template = Template::create($data);

        $template->addLog(
            new RequestLogContext($request),
            'create'
        );

        return redirect()->to('/admin/templates');
    }

    public function editTemplate(Request $request, Template $template)
    {
        $this->authorize('update', $template);

        /** @var User $user */
        $user = $request->user();

        $wikis = Wiki::get()
            ->filter(function (Wiki $wiki) use ($user) {
                return $user->can('create', [Template::class, $wiki]);
            })
            ->mapWithKeys(function (Wiki $wiki) {
                return [$wiki->id => $wiki->display_name . ' (' . $wiki->database_name . ')'];
            });

        return view('admin.edittemplate', ["template" => $template, 'wikis' => $wikis]);
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
            'wiki_id' => 'required|exists:wikis,id',
        ]);

        $logText = 'update';

        if ($data['wiki_id'] != $template->wiki_id) {
            $newWiki = Wiki::findOrFail($data['wiki_id']);
            $this->authorize('create', [Template::class, $newWiki]);
            $logText .= ', change wiki to ' . $newWiki->database_name;
        }

        $template->update($data);
        $template->addLog(
            new RequestLogContext($request),
            $logText
        );

        return redirect()->to('/admin/templates');
    }
}
