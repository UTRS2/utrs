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

        $date = Carbon::now()->subDays(90);
        $en_blockadmin = \Lava::DataTable();
        $en_blockadmin->addStringColumn('Administrator')
            ->addNumberColumn('Number of times they are blocking admins');
        $admins = [];
        foreach ($enwiki->where('blockfound',1)->where('submitted', '>',Carbon::now()->subDays(90)) as $appeal) {
            if (!isset($admins[$appeal->blockingadmin])) {
                $admins[$appeal->blockingadmin] = 1;
            } else {
                $admins[$appeal->blockingadmin] = $admins[$appeal->blockingadmin] + 1;
            }
        }
        //go through $admins and remove any with a count of less than 10
        foreach ($admins as $admin => $count) {
            if ($count < 15) {
                unset($admins[$admin]);
            }
        }
        //sort the array by the number of times they are blocking admins
        arsort($admins);
        foreach ($admins as $admin => $count) {
            $en_blockadmin->addRow([$admin, $count]);
        }
        \Lava::BarChart('en_admincount', $en_blockadmin, [
            'title' => 'Number of requests per block admin if over 15 appeals in last 90 days - enwiki',
            'legend' => [
                'position' => 'none'
            ],
            'colors' => ['#0000FF'],
            'height' => 1500,
            'width' => 1000,
        ]);

        $date = Carbon::now()->subDays(90);
        $en_blockreason = \Lava::DataTable();
        $en_blockreason->addStringColumn('Reason')
            ->addNumberColumn('Number of times a reason was used');
        $reasons = [];
        $reasons['other'] = 0;
        foreach ($enwiki->where('blockfound',1)->where('submitted', '>',Carbon::now()->subDays(90)) as $appeal) {
            //if reason has wikimarkup for a template, get the template name, and count them
            if (preg_match('/\{\{([^\}]+)\}\}/', $appeal->blockreason, $matches)) {
                if (!isset($reasons[$matches[1]])) {
                    $reasons["{{".$matches[1]."}}"] = 1;
                } else {
                    $reasons["{{".$matches[1]."}}"] = $reasons[$matches[1]] + 1;
                }
            } else {
                //if reason doesn't have wikimarkup for a template, take anything before the last ":", outside of a wikilink, and count them, otherwise ignore it
                if (preg_match('/\[\[([^\]]+)\]\]/', $appeal->blockreason, $matches)) {
                    $reason = substr($matches[1], strrpos($matches[1], ':') + 1);
                    if (!isset($reasons[$reason])) {
                        $reasons[$reason] = 1;
                    } else {
                        $reasons[$reason] = $reasons[$reason] + 1;
                    }
                } else {
                    $reason = substr($appeal->blockreason, strrpos($appeal->blockreason, ':') + 1);
                    if (!isset($reasons[$reason])) {
                        $reasons[$reason] = 1;
                    } else {
                        $reasons[$reason] = $reasons[$reason] + 1;
                    }
                }
            }
        }
        //go through $reasons and remove any with a count of less than 10 and sort by count
        foreach ($reasons as $reason => $count) {
            if ($count < 10) {
                unset($reasons[$reason]);
            }
        }
        arsort($reasons);
        foreach ($reasons as $reason => $count) {
            $en_blockreason->addRow([$reason, $count]);
        }
        \Lava::BarChart('en_blockreason', $en_blockreason, [
            'title' => 'Number of requests per block reason if over 10 appeals in last 90 days - enwiki',
            'legend' => [
                'position' => 'none'
            ],
            'colors' => ['#0000FF'],
            'height' => 1500,
            'width' => 1000,
        ]);

        return view('stats.appeals');

    }
}
