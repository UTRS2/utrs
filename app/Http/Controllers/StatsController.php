<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appeal;
use Carbon\Carbon;
use Khill\Lavacharts\Laravel\LavachartsFacade as Lava;

class StatsController extends Controller
{
    public function display_appeals_chart(Request $request)
    {
        $requestedChart = $request->name;
        $requestedWiki = $request->wiki;
        $requestedLength = $request->length;
        
        //if any of the variables are not set, set a default for each
        if ($requestedChart == null) {
            $requestedChart = 'apppd';
        }
        if ($requestedWiki == null) {
            $requestedWiki = 'enwiki';
        }
        if ($requestedLength == null) {
            $requestedLength = '90d';
        }

        $acceptedChartNames = [
            'apppd'/*appeals per day*/,
            'blkadm'/*blocking admin*/,
            'blkreason', /*block reason*/
            'appstate'/*appeal state*/
        ];
        if (!in_array($requestedChart, $acceptedChartNames)) {
            return abort(404);
        }
        $acceptedWikiNames = ['enwiki', 'global', 'all'];
        if (!in_array($requestedWiki, $acceptedWikiNames)) {
            return abort(404);
        }
        $acceptedLengths = ['7d', '30d', '90d', '180d', '365d'];
        if (!in_array($requestedLength, $acceptedLengths)) {
            return abort(404);
        }
        $numericDay = (int) explode('d', $requestedLength)[0];

        if ($requestedWiki == 'enwiki') {
            $wiki_id = 1;
        } elseif ($requestedWiki == 'global') {
            $wiki_id = 3;
        } else {
            $wiki_id = null;
        }
        //if no wiki id is set, then get all appeals in the time period
        if ($wiki_id == null) {
            $dbdata = Appeal::whereDate('submitted', '>',Carbon::now()->subDays($numericDay))->get();
        } else {
            $dbdata = Appeal::whereDate('submitted', '>',Carbon::now()->subDays($numericDay))->where('wiki_id',$wiki_id)->get();
        }
        if($requestedChart == 'apppd') {
            $date = Carbon::now()->subDays($numericDay);
            $chart_data = \Lava::DataTable();
            $chart_data->addDateColumn('Date')
                ->addNumberColumn('Appeals')
                ->setDateTimeFormat('Y-m-d');
            for ($i = 0; $i < $numericDay; $i++) {
                $chart_data->addRow([$date->format('Y-m-d'), $dbdata->where('blockfound',1)->where('submitted', '>', $date)->where('submitted', '<', $date->addDays(1))->count()]);
                
            }

            \Lava::ColumnChart('perday', $chart_data, [
                'title' => 'Per days appeals in the last '.$numericDay.' days where the block was found - '.$requestedWiki,
                'legend' => [
                    'position' => 'none'
                ],
                'colors' => ['#0000FF'],
                'height' => 500,
                'width' => 1000,
            ]);
        }

        if ($requestedChart == 'appstate') {
            $chart_data = \Lava::DataTable();
            $chart_data->addStringColumn('global_appstat')
                ->addNumberColumn('Number of appeals')
                ->addRow(['Total appeals in time period', $dbdata->count()])
                ->addRow(['Accepted', $dbdata->where('status', Appeal::STATUS_ACCEPT)->count()])
                ->addRow(['Declined', $dbdata->where('status', Appeal::STATUS_DECLINE)->count()])
                ->addRow(['Expired', $dbdata->where('status', Appeal::STATUS_EXPIRE)->count()])
                ->addRow(['Still under review', $dbdata->where('status', Appeal::STATUS_OPEN)->count()]);

            \Lava::BarChart('appstat', $chart_data, [
                'title' => 'Appeals in the last '.$numericDay.' days - Global',
                'legend' => [
                    'position' => 'none'
                ],
                'colors' => ['#FF0000', '#0000FF'],
                'height' => 500,
                'width' => 1000,
            ]);
        }

        if ($requestedChart == 'blkadm') {
            $date = Carbon::now()->subDays(90);
            $chart_data = \Lava::DataTable();
            $chart_data->addStringColumn('Administrator')
                ->addNumberColumn('Number of times they are blocking admins');
            $admins = [];
            foreach ($dbdata->where('blockfound',1)->where('submitted', '>',Carbon::now()->subDays(90)) as $appeal) {
                if (!isset($admins[$appeal->blockingadmin])) {
                    $admins[$appeal->blockingadmin] = 1;
                } else {
                    $admins[$appeal->blockingadmin] = $admins[$appeal->blockingadmin] + 1;
                }
            }
            //go through $admins and remove any with a count of less than 10
            foreach ($admins as $admin => $count) {
                if ($count < 15 && $requestedWiki != 'global') {
                    unset($admins[$admin]);
                }
                elseif ($count < 2 && $requestedWiki == 'global') {
                    unset($admins[$admin]);
                }
            }
            //sort the array by the number of times they are blocking admins
            arsort($admins);
            foreach ($admins as $admin => $count) {
                $chart_data->addRow([$admin, $count]);
            }
            \Lava::BarChart('admincount', $chart_data, [
                'title' => 'Number of requests per block admin if over 15 appeals in last '.$numericDay.' days - '.$requestedWiki,
                'legend' => [
                    'position' => 'none'
                ],
                'colors' => ['#0000FF'],
                'height' => 1500,
                'width' => 1000,
            ]);
        }

        if ($requestedChart == 'blkreason') {
            $chart_data = \Lava::DataTable();
            $chart_data->addStringColumn('Reason')
                ->addNumberColumn('Number of times a reason was used in the last '.$numericDay.' days');
            $reasons = [];
            $other = 0;
            foreach ($dbdata as $appeal) {
                //make $appeal->blockreason lower case
                $blockreason = strtolower($appeal->blockreason);
                //if reason has wikimarkup for a template, get the template name, and count them
                if (preg_match('/\{\{.*\}\}/', $blockreason, $matches)) {
                    //if "|" is in the template, then only use the text before the pipe
                    if (preg_match('/\{\{.*\|.*/', $matches[0], $matchesnew)) {
                        $blockreason = explode('|', $matchesnew[0])[0].'}}';
                    }
                    if (isset(explode('}}', $blockreason)[0])) {
                        $blockreason = explode('}}', $blockreason)[0].'}}';
                    }
                    //if block reason contains "prox", then set it to "open proxy"
                    if (preg_match('/prox/', $blockreason, $matches)) {
                        $blockreason = 'open proxy';
                    }
                    if ($blockreason == null) {
                        dd($appeal);
                        $reasons["Other uncatigorizable"] = $reasons[$blockreason] + 1;
                    }
                    elseif (isset($reasons[$blockreason])) {
                        $reasons[$blockreason] = $reasons[$blockreason] + 1;
                    } else {
                        $reasons[$blockreason] = 1;
                    }
                } else {
                    $link = null;
                    //if there is a wikilink store it in a variable named $link
                    if (preg_match('/\[\[(:|)(wp|wikipedia|m)\:.*\]\]/', $blockreason, $matches)) {
                        $link = $matches[0];
                        //split the match by "]]" and get the first part
                        $link = explode(']]', $link)[0];
                    }
                    //if $link is set
                    if ($link!=null) {
                        //if the wikilink has a pipe, then only use the text after the pipe
                        if (preg_match('/\|/', $link, $matches)) {
                            $blockreason = explode('|', $link)[1];
                        }
                        //if block reason contains "prox", then set it to "open proxy"
                        if (preg_match('/prox/', $blockreason, $matches)) {
                            $blockreason = 'open proxy';
                        }
                        if ($blockreason == null) {
                            dd($appeal);
                            $reasons["Other uncatigorizable"] = $reasons[$blockreason] + 1;
                        }
                        elseif (isset($reasons[$blockreason])) {
                            $reasons[$blockreason] = $reasons[$blockreason] + 1;
                        } else {
                            $reasons[$blockreason] = 1;
                        }
                    } else {
                        //if requested wiki is global, then still add the reason to the array
                        if ($requestedWiki == 'global') {
                            //if there is a ":" in the block reason, then only use the text before the ":"
                            if (preg_match('/:/', $blockreason, $matches)) {
                                $blockreason = explode(':', $blockreason)[0];
                            }
                            //if block reason contains "prox", then set it to "open proxy"
                            if (preg_match('/prox/', $blockreason, $matches)) {
                                $blockreason = 'open proxy';
                            }
                            if ($blockreason == null) {
                                dd($appeal);
                                $reasons["Other uncatigorizable"] = $reasons[$blockreason] + 1;
                            }
                            elseif (isset($reasons[$blockreason])) {
                                $reasons[$blockreason] = $reasons[$blockreason] + 1;
                            } else {
                                $reasons[$blockreason] = 1;
                            }
                        } else {
                            //if there is no wikilink or template, then just add it to the other category
                            $other = $other + 1;
                        }
                    }
                    
                }
            }
            //go through $reasons and remove any with a count of less than 10 and sort by count
            foreach ($reasons as $reason => $count) {
                if ($count < 10 && $requestedWiki != 'global') {
                    unset($reasons[$reason]);
                }
                elseif ($count < 5 && $requestedWiki == 'global') {
                    unset($reasons[$reason]);
                }
            }
            arsort($reasons);
            foreach ($reasons as $reason => $count) {
                $chart_data->addRow([$reason, $count]);
            }
            \Lava::BarChart('blockreason', $chart_data, [
                'title' => 'Number of requests per block reason if over 10 appeals in last '.$numericDay.' days - '.$requestedWiki,
                'legend' => [
                    'position' => 'none'
                ],
                'colors' => ['#0000FF'],
                'height' => 1700,
                'width' => 1300,
            ]);
        }

        $chartlinks = [
            'apppd' => '/statistics/apppd/'.$requestedWiki.'/'.$requestedLength,
            'blkadm' => '/statistics/blkadm/'.$requestedWiki.'/'.$requestedLength,
            'blkreason' => '/statistics/blkreason/'.$requestedWiki.'/'.$requestedLength,
            'appstate' => '/statistics/appstate/'.$requestedWiki.'/'.$requestedLength,
        ];
        $timelinks = [
            '7d' => '/statistics/'.$requestedChart.'/'.$requestedWiki.'/7d',
            '30d' => '/statistics/'.$requestedChart.'/'.$requestedWiki.'/30d',
            '90d' => '/statistics/'.$requestedChart.'/'.$requestedWiki.'/90d',
            '180d' => '/statistics/'.$requestedChart.'/'.$requestedWiki.'/180d',
            '365d' => '/statistics/'.$requestedChart.'/'.$requestedWiki.'/365d',
        ];
        $wikilinks = [
            'enwiki' => '/statistics/'.$requestedChart.'/enwiki/'.$requestedLength,
            'global' => '/statistics/'.$requestedChart.'/global/'.$requestedLength,
            'all' => '/statistics/'.$requestedChart.'/all/'.$requestedLength,
        ];

        return view('stats.appeals', ['chart'=>$requestedChart, 'wiki'=>$requestedWiki, 'chartlinks'=>$chartlinks, 'timelinks'=>$timelinks, 'wikilinks'=>$wikilinks]);

    }
}
