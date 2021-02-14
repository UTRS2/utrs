<?php

namespace App\Http\Controllers;

use App\Models\Wiki;
use Illuminate\Http\Request;

class WikiController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Wiki::class);

        $wikis = Wiki::orderBy('database_name')->get();
        return view('wikis.list', ['wikis' => $wikis]);
    }
}
