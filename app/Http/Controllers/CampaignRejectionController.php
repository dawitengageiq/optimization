<?php

namespace App\Http\Controllers;

use App\Campaign;
use App\CampaignFullRejectionStatistics;
use App\CampaignRejectionStatistic;
use App\Helpers\GetDateByRangeHelper;
use App\Setting;
use DB;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Log;

class CampaignRejectionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function view(Request $request): View
    {
        $settings = Setting::where('code', 'high_critical_rejection_rate')->first();
        $high_rates = json_decode($settings->string_value);
        $high_min = $high_rates[0];
        $high_max = $high_rates[1];
        $per_group = 7;
        $hasData = false;

        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');
        $range = $request->input('date_range');
        if ($date_from == '' && $date_to == '' && $range == '') {
            $range = 'month';
        }
        if ($range != '') {
            $ranger = new GetDateByRangeHelper($range);
            $date_from = $ranger->date_from();
            $date_to = $ranger->date_to();
        }
        // Log::info('Get Date');
        // Log::info($date_from);
        // Log::info($date_to);

        $data = CampaignRejectionStatistic::whereBetween('created_at', [$date_from, $date_to])
            ->select(DB::RAW('campaign_id, SUM(total_count) as total_count, SUM(reject_count) as reject_count, SUM(acceptable_reject_count) as acceptable_reject_count, SUM(duplicate_count) as duplicate_count, SUM(filter_count) as filter_count, SUM(prepop_count) as prepop_count, SUM(other_count) as other_count'))
            ->groupBy('campaign_id')->orderBy('total_count', 'DESC')->get();

        $process = [];
        $stats = [];
        $categories = [];
        $cids = [];
        $actual = [];
        foreach ($data as $d) {
            $cids[] = $d->campaign_id;
            $reject_rate = ($d->reject_count / $d->total_count) * 100;

            if ($reject_rate >= $high_min && $reject_rate <= $high_max) {
                $type = 'high';
            } else {
                $type = 'critical';
            }

            $process[$type][] = $d;
        }

        foreach ($process as $type => $d) {
            $count = 1;
            $current = 0;
            foreach ($d as $dd) {
                $hasData = true;

                if ($count > $per_group) {
                    $count = 1;
                    $current++;
                }

                if (! isset($stats[$type][$current])) {
                    $stats[$type][$current] = [
                        [
                            'name' => 'LEADS',
                            'data' => [],
                            'stack' => 'stack1',
                        ],
                        [
                            'name' => 'ACCEPTABLE',
                            'data' => [],
                            'stack' => 'stack2',
                        ],
                        [
                            'name' => 'DUPLICATES',
                            'data' => [],
                            'stack' => 'stack2',
                        ],
                        [
                            'name' => 'FILTER ISSUE',
                            'data' => [],
                            'stack' => 'stack2',
                        ],
                        [
                            'name' => 'PRE-POP ISSUE',
                            'data' => [],
                            'stack' => 'stack2',
                        ],
                        [
                            'name' => 'OTHERS',
                            'data' => [],
                            'stack' => 'stack2',
                        ],
                    ];
                }

                $stats[$type][$current][0]['data'][] = $dd->total_count;
                $stats[$type][$current][1]['data'][] = $dd->acceptable_reject_count;
                $stats[$type][$current][2]['data'][] = $dd->duplicate_count;
                $stats[$type][$current][3]['data'][] = $dd->filter_count;
                $stats[$type][$current][4]['data'][] = $dd->prepop_count;
                $stats[$type][$current][5]['data'][] = $dd->other_count;

                $categories[$type][$current][] = $dd->campaign_id;
                $actual[$dd->campaign_id] = $dd;
                $count++;
            }
        }

        //$date = Carbon::parse($date)->toFormattedDateString();
        // Flag for script process
        if ($date_from != $date_to) {
            $dateDisplay = $date_from.' - '.$date_to;
        } else {
            $dateDisplay = $date_from;
        }
        $highcharts = ['date' => $dateDisplay, 'date_from' => $date_from, 'date_to' => $date_to, 'series' => $stats, 'categories' => $categories, 'campaigns' => Campaign::whereIn('id', $cids)->pluck('name', 'id'), 'has_data' => $hasData, 'actual' => $actual];
        $inputs = $request->all();
        $inputs['date_range'] = $range;

        return view('admin.campaign_rejection', compact('highcharts', 'inputs'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function full_rejection_view(Request $request)
    {
        $settings = Setting::where('code', 'high_critical_rejection_rate')->first();
        $high_rates = json_decode($settings->string_value);
        $high_min = $high_rates[0];
        $high_max = $high_rates[1];
        $per_group = 7;
        $hasData = false;

        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');
        $range = $request->input('date_range');
        if ($date_from == '' && $date_to == '' && $range == '') {
            $range = 'month';
        }
        if ($range != '') {
            $ranger = new GetDateByRangeHelper($range);
            $date_from = $ranger->date_from();
            $date_to = $ranger->date_to();
        }
        // Log::info('Get Date');
        // Log::info($date_from);
        // Log::info($date_to);
        // \DB::enableQueryLog();
        // \DB::connection('secondary')->enableQueryLog();
        $data = CampaignFullRejectionStatistics::whereBetween('created_at', [$date_from, $date_to])
            ->select(DB::RAW('campaign_id, SUM(total_count) as total_count, SUM(reject_count) as reject_count, SUM(acceptable_reject_count) as acceptable_reject_count, SUM(duplicate_count) as duplicate_count, SUM(filter_count) as filter_count, SUM(prepop_count) as prepop_count, SUM(other_count) as other_count'))
            // ->where(DB::RAW('total_count'), '=', DB::RAW('reject_count'))
            ->groupBy('campaign_id')->orderBy('total_count', 'DESC')->get();
        // \Log::info(\DB::getQueryLog());
        // \Log::info(\DB::connection('secondary')->getQueryLog());
        //\Log::info($data);
        $process = [];
        $stats = [];
        $categories = [];
        $cids = [];
        $actual = [];
        foreach ($data as $d) {
            $cids[] = $d->campaign_id;
            $reject_rate = ($d->reject_count / $d->total_count) * 100;

            if ($reject_rate >= $high_min && $reject_rate <= $high_max) {
                $type = 'high';
            } else {
                $type = 'critical';
            }

            $process[$type][] = $d;
        }
        // \Log::info(count($data));

        foreach ($process as $type => $d) {
            $count = 1;
            $current = 0;
            foreach ($d as $dd) {
                $hasData = true;

                if ($count > $per_group) {
                    $count = 1;
                    $current++;
                }

                if (! isset($stats[$type][$current])) {
                    $stats[$type][$current] = [
                        [
                            'name' => 'LEADS',
                            'data' => [],
                            'stack' => 'stack1',
                        ],
                        [
                            'name' => 'ACCEPTABLE',
                            'data' => [],
                            'stack' => 'stack2',
                        ],
                        [
                            'name' => 'DUPLICATES',
                            'data' => [],
                            'stack' => 'stack2',
                        ],
                        [
                            'name' => 'FILTER ISSUE',
                            'data' => [],
                            'stack' => 'stack2',
                        ],
                        [
                            'name' => 'PRE-POP ISSUE',
                            'data' => [],
                            'stack' => 'stack2',
                        ],
                        [
                            'name' => 'OTHERS',
                            'data' => [],
                            'stack' => 'stack2',
                        ],
                    ];
                }

                $stats[$type][$current][0]['data'][] = $dd->total_count;
                $stats[$type][$current][1]['data'][] = $dd->acceptable_reject_count;
                $stats[$type][$current][2]['data'][] = $dd->duplicate_count;
                $stats[$type][$current][3]['data'][] = $dd->filter_count;
                $stats[$type][$current][4]['data'][] = $dd->prepop_count;
                $stats[$type][$current][5]['data'][] = $dd->other_count;

                $categories[$type][$current][] = $dd->campaign_id;
                $actual[$dd->campaign_id] = $dd;
                $count++;
            }
        }

        //$date = Carbon::parse($date)->toFormattedDateString();
        // Flag for script process
        if ($date_from != $date_to) {
            $dateDisplay = $date_from.' - '.$date_to;
        } else {
            $dateDisplay = $date_from;
        }
        $highcharts = ['date' => $dateDisplay, 'date_from' => $date_from, 'date_to' => $date_to, 'series' => $stats, 'categories' => $categories, 'campaigns' => Campaign::whereIn('id', $cids)->pluck('name', 'id'), 'has_data' => $hasData, 'actual' => $actual];
        $inputs = $request->all();
        $inputs['date_range'] = $range;

        // return $highcharts;
        return view('admin.full_rejection_campaign_rejection', compact('highcharts', 'inputs'));
    }
}
