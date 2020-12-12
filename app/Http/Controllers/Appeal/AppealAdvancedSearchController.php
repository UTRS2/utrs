<?php

namespace App\Http\Controllers\Appeal;

use App\Http\Controllers\Controller;
use App\Models\Appeal;
use Illuminate\Http\Request;

class AppealAdvancedSearchController extends Controller
{
    public function search(Request $request)
    {
        $this->authorize('viewAny', Appeal::class);

        return view('appeals.search.search');
    }
}
