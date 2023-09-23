<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appeal;
use Carbon\Carbon;
use Khill\Lavacharts\Laravel\LavachartsFacade as Lava;

class StatsController extends Controller
{
    public function display()
    {
        return view('stats');
    }

    public function display_appeals_chart()
    {
        $results = Appeal::whereTime('submitted', '>',Carbon::now()->subDays(90))->get();
        $data = \Lava::DataTable();
        $data = $data->addStringColumn('appstat')
            ->addNumberColumn('Number of appeals')
            ->addRow(['Total appeals in time period', $results->count()])
            ->addRow(['Accepted', $results->where('status', Appeal::STATUS_ACCEPT)->count()])
            ->addRow(['Declined', $results->where('status', Appeal::STATUS_DECLINE)->count()])
            ->addRow(['Expired', $results->where('status', Appeal::STATUS_EXPIRE)->count()]);

        \Lava::BarChart('appstat', $data, [
            'title' => 'Appeals in the last 90 days',
            'legend' => [
                'position' => 'none'
            ],
            'colors' => ['#000000', '#00FF00', '#FF0000', '#0000FF'],
            'height' => 500,
            'width' => 1000,
        ]);
        return view('stats.appeals', ['chart' => $data]);
    }
}
