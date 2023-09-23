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
        $enwiki = Appeal::whereTime('submitted', '>',Carbon::now()->subDays(90))->where('wiki_id',1)->get();
        
        $en_data = \Lava::DataTable();
        $en_data = $en_data->addStringColumn('enwiki_appstat')
            ->addNumberColumn('Number of appeals')
            ->addRow(['Total appeals in time period', $enwiki->count()])
            ->addRow(['Accepted', $enwiki->where('status', Appeal::STATUS_ACCEPT)->count()])
            ->addRow(['Declined', $enwiki->where('status', Appeal::STATUS_DECLINE)->count()])
            ->addRow(['Expired', $enwiki->where('status', Appeal::STATUS_EXPIRE)->count()]);

        \Lava::BarChart('enwiki_appstat', $en_data, [
            'title' => 'Appeals in the last 90 days - enwiki',
            'legend' => [
                'position' => 'none'
            ],
            'colors' => ['#00FF00', '#FF0000', '#0000FF'],
            'height' => 500,
            'width' => 1000,
        ]);

        $global = Appeal::whereTime('submitted', '>',Carbon::now()->subDays(90))->where('wiki_id',3)->get();
        $g_data = \Lava::DataTable();
        $g_data = $g_data->addStringColumn('global_appstat')
            ->addNumberColumn('Number of appeals')
            ->addRow(['Total appeals in time period', $global->count()])
            ->addRow(['Accepted', $global->where('status', Appeal::STATUS_ACCEPT)->count()])
            ->addRow(['Declined', $global->where('status', Appeal::STATUS_DECLINE)->count()])
            ->addRow(['Expired', $global->where('status', Appeal::STATUS_EXPIRE)->count()]);

        \Lava::BarChart('global_appstat', $g_data, [
            'title' => 'Appeals in the last 90 days - Global',
            'legend' => [
                'position' => 'none'
            ],
            'colors' => ['#FF0000', '#0000FF'],
            'height' => 500,
            'width' => 1000,
        ]);
        return view('stats.appeals');
    }
}
