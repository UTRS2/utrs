<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appeal;
use App\Models\Template;
use App\Models\User;
use App\Models\Wiki;
use App\Utils\Logging\RequestLogContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TemplateController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}

	public function index(Request $request)
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

		return view('admin.tables', [
			'title' => 'All Templates',
			'tableheaders' => $tableheaders,
			'rowcontents' => $rowcontents,
			'new' => true,
			'createlink' => '/admin/templates/create',
			'createtext' => 'New template'
		]);
	}

	public function new(Request $request)
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

	public function create(Request $request)
	{
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

	public function show(Request $request, Template $template)
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

	public function update(Request $request, Template $template)
	{
		$this->authorize('update', $template);

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