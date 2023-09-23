<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appeal;
use Carbon\Carbon;

class StatsController extends Controller
{
    public function display()
    {
        return view('stats');
    }

    public function display_appeals_chart()
    {
        $results = Appeal::whereTime('submitted', '>',Carbon::now()->subDays(90))->get();
        dd($results);
        return view('stats_appeals_chart');
    }
}
