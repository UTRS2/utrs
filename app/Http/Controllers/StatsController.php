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
        $enwiki = Appeal::whereDate('submitted', '>',Carbon::now()->subDays(90))->where('wiki_id',1)->get();

        $date = Carbon::now()->subDays(90);
        $en_perday = \Lava::DataTable();
        $en_perday->addDateColumn('Date')
            ->addNumberColumn('Appeals')
            ->setDateTimeFormat('Y-m-d');
        for ($i = 0; $i < 90; $i++) {
            $en_perday->addRow([$date->format('Y-m-d'), $enwiki->where('blockfound',1)->where('submitted', '>', $date)->where('submitted', '<', $date->addDays(1))->count()]);
            
        }

        \Lava::ColumnChart('enwiki_daystat', $en_perday, [
            'title' => 'Per days appeals in the last 90 days where the block was found - enwiki',
            'legend' => [
                'position' => 'none'
            ],
            'colors' => ['#0000FF'],
            'height' => 500,
            'width' => 1000,
        ]);


        
        $en_data = \Lava::DataTable();
        $en_data = $en_data->addStringColumn('enwiki_appstat')
            ->addNumberColumn('Number of appeals')
            ->addRow(['Total appeals in time period', $enwiki->count()])
            ->addRow(['Accepted', $enwiki->where('status', Appeal::STATUS_ACCEPT)->count()])
            ->addRow(['Declined', $enwiki->where('status', Appeal::STATUS_DECLINE)->count()])
            ->addRow(['Expired', $enwiki->where('status', Appeal::STATUS_EXPIRE)->count()])
            ->addRow(['Still under review', $enwiki->where('status', Appeal::STATUS_OPEN)->count()]);

        \Lava::BarChart('enwiki_appstat', $en_data, [
            'title' => 'Appeals in the last 90 days - enwiki',
            'legend' => [
                'position' => 'none'
            ],
            'colors' => ['#00FF00', '#FF0000', '#0000FF'],
            'height' => 500,
            'width' => 1000,
        ]);

        $global = Appeal::whereDate('submitted', '>',Carbon::now()->subDays(90))->where('wiki_id',3)->get();
        $g_data = \Lava::DataTable();
        $g_data = $g_data->addStringColumn('global_appstat')
            ->addNumberColumn('Number of appeals')
            ->addRow(['Total appeals in time period', $global->count()])
            ->addRow(['Accepted', $global->where('status', Appeal::STATUS_ACCEPT)->count()])
            ->addRow(['Declined', $global->where('status', Appeal::STATUS_DECLINE)->count()])
            ->addRow(['Expired', $global->where('status', Appeal::STATUS_EXPIRE)->count()])
            ->addRow(['Still under review', $global->where('status', Appeal::STATUS_OPEN)->count()]);

        \Lava::BarChart('global_appstat', $g_data, [
            'title' => 'Appeals in the last 90 days - Global',
            'legend' => [
                'position' => 'none'
            ],
            'colors' => ['#FF0000', '#0000FF'],
            'height' => 500,
            'width' => 1000,
        ]);

        $date = Carbon::now()->subDays(90);
        $en_perday = \Lava::DataTable();
        $en_perday->addDateColumn('Date')
            ->addNumberColumn('Appeals')
            ->setDateTimeFormat('Y-m-d');
        for ($i = 0; $i < 90; $i++) {
            $en_perday->addRow([$date->format('Y-m-d'), $global->where('blockfound',1)->where('submitted', '>', $date)->where('submitted', '<', $date->addDays(1))->count()]);
            
        }

        \Lava::ColumnChart('global_daystat', $en_perday, [
            'title' => 'Per days appeals in the last 90 days where the block was found - global',
            'legend' => [
                'position' => 'none'
            ],
            'colors' => ['#0000FF'],
            'height' => 500,
            'width' => 1000,
        ]);

        return view('stats.appeals',['dates'=>$dates]);

    }
}
