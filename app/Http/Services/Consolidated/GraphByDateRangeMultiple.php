<?php

namespace App\Http\Services\Consolidated;

use App\ConsolidatedGraph;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

/**
 * Consolidate graph class.
 * Extended by GraphByRevenueTrackerID::class
 */
class GraphByDateRangeMultiple extends GraphByDateRange implements \App\Http\Services\Contracts\ConsolidatedGraphContract
{
    /**
     * Revenue tracker id container, use for filtering Consolidated data query.
     *
     * @var string
     */
    protected $revenueTrackerIDs = [];

    /**
     * Range: Yesterday, Week to date, Month to date, All of last Month
     *
     * @var string
     */
    protected $predefineDates = '';

    /**
     * Selected columns container
     *
     * @var array
     */
    protected $columns = [
        'all_clicks',
        'survey_takers',
        'cost_per_all_clicks',
        'survey_takers_per_clicks',
        'source_revenue',
        'cost',
        'margin',
        'source_revenue_per_all_clicks',
        'source_revenue_per_survey_takers',
        'all_inbox_revenue',
        'all_inbox_per_survey_takers',
        'all_coreg_revenue',
        'all_coreg_revenue_per_all_coreg_views',
        'coreg_p1_revenue_vs_views',
        'coreg_p2_revenue_vs_views',
        'coreg_p3_revenue_vs_views',
        'coreg_p4_revenue_vs_views',
        'all_mp_revenue',
        'mp_per_views',
        'pd_revenue',
        'pd_revenue_vs_views',
        'tb_revenue',
        'tb_revenue_vs_views',
        'iff_revenue',
        'iff_revenue_vs_views',
        'rexadz_revenue',
        'rexadz_revenue_vs_views',
        // 'adsmith_revenue',
        // 'adsmith_revenue_vs_views',
        'push_revenue',
        'push_cpa_revenue_per_survey_takers',
        'cpa_revenue',
        'cpa_revenue_per_views',
        'lsp_revenue',
        'lsp_revenue_vs_views',
        // 'lsp_views',
        // 'coreg_p1_revenue',
        // 'coreg_p2_revenue',
        // 'coreg_p3_revenue',
        // 'coreg_p4_revenue',
        // 'cpa_per_survey_takers',
    ];

    protected $subid_includes = [
        's1' => true,
        's2' => true,
        's3' => true,
        's4' => true,
    ];

    /**
     * Instantiate.
     * Provide the eloquent model.
     */
    public function __construct(ConsolidatedGraph $model, Carbon $carbon)
    {
        $this->model = $model;
        $this->carbon = $carbon;
    }

    /**
     * Set the revenue tracker, provided by Controller.
     */
    public function setRevenueTrackerID($revenueTrackerIDs)
    {
        if (is_array($revenueTrackerIDs)) {
            $revenueTrackerIDs = array_filter($revenueTrackerIDs);
        }
        if (! $revenueTrackerIDs) {
            return;
        }

        $this->revenueTrackerIDs = $revenueTrackerIDs;
    }

    /**
     * Set legends, provided by controller from config.consolidatedgraph.legends.
     * We have to set immediatly the counters for generating chart series.
     */
    public function setLegends(array $legends)
    {
        $this->legends = $legends;

        $this->setcounters();
    }

    /**
     * Pre define dates, range: Yesterday, Week to date, Month to date, All of last Month
     */
    public function setPredefineDates($predefineDates)
    {
        $this->predefineDates = $predefineDates;
    }

    /**
     * Get the consilidated data to be use in chart.
     *
     * @model ConsolidatedGraph.
     */
    public function getConsolidatedData()
    {
        // \Log::info('Multi');
        // $selectQry = 'consolidated_graph.*';
        // if(count($this->columns)) {
        //     $select = $this->col
        //     $s = [];
        //     foreach($this->columns as $c) {
        //         $s[] = "SUM($c) as $c";
        //     }

        //     $s[] = 'revenue_tracker_id';
        //     $s[] = 'id';
        //     $s[] = 's1';
        //     $s[] = 's2';
        //     $s[] = 's3';
        //     $s[] = 's4';
        //     $s[] = 's5';

        //     $selectQry = implode(', ' , $s);
        // }

        $selectQry = $this->getColumnSumQry();
        //\Log::info($select);

        $query = $this->model->select(DB::RAW($selectQry));

        // $query = $this->model->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as date'), ...$select);
        // Selected revenue trackers; else all
        if (count($this->revenueTrackerIDs)) {
            $query->whereIn('revenue_tracker_id', $this->revenueTrackerIDs);
        }
        // Date range by date picker
        if (! $this->predefineDates) {
            $this->dateFrom = Carbon::parse($this->dateFrom)->startOfDay();
            $this->dateTo = Carbon::parse($this->dateTo)->endOfDay();
            $query->whereBetween('created_at', [$this->dateFrom, $this->dateTo]);
        }
        // Date range by pre define dates
        else {
            $query = $this->{'query'.ucwords(camelCase($this->predefineDates))}($query);
        }

        $query->groupBy('date');
        $query->groupBy('revenue_tracker_id');
        if ($this->subid_includes['s1']) {
            $query->groupBy('s1');
        }
        if ($this->subid_includes['s2']) {
            $query->groupBy('s2');
        }
        if ($this->subid_includes['s3']) {
            $query->groupBy('s3');
        }
        if ($this->subid_includes['s4']) {
            $query->groupBy('s4');
        }

        $records = [];
        $this->records = $query
            ->orderBy('revenue_tracker_id', 'ASC')
            ->orderBy('date', 'ASC')
            ->orderBy('s1', 'ASC')
            ->orderBy('s2', 'ASC')
            ->orderBy('s3', 'ASC')
            ->orderBy('s4', 'ASC')
            ->get()
            // ->groupBy(function($data) {
            //      return $this->carbon->parse($data->created_at)->format('Y-m-d');
            // })
            ->toArray();
        // foreach($graphs as $graph) {
        //     $data = $graph;

        //     //get Survey Takers / Clicks
        //     $graph['survey_takers_per_clicks'] = number_format($graph['all_clicks'] != 0 && $graph['survey_takers'] != 0 ? ($graph['survey_takers'] / $graph['all_clicks']) : 0.00, 2);

        //     //get Cost / All Clicks
        //     $graph['cost_per_all_clicks'] = number_format($graph['all_clicks'] != 0 && $graph['cost'] != 0 ? ($graph['cost'] / $graph['all_clicks']) : 0.00, 2);

        //     //get Margin
        //     $graph['margin'] = number_format($graph['source_revenue'] != 0 && $graph['cost'] != 0 ? (($graph['source_revenue'] - $graph['cost']) / $graph['source_revenue']) : 0.00, 2);

        //     //get Revenue / All Clicks
        //     $graph['source_revenue_per_all_clicks'] = number_format($graph['source_revenue'] != 0 && $graph['all_clicks'] != 0 ? $graph['source_revenue'] / $graph['all_clicks']  : 0.00 , 2);

        //     //get Revenue / Survey Takers
        //     $graph['source_revenue_per_survey_takers'] = number_format($graph['source_revenue'] != 0 && $graph['survey_takers'] != 0 ? $graph['source_revenue'] / $graph['survey_takers'] : 0.00, 2);

        //     //get All Coreg Revenue / All Views
        //     $graph['all_coreg_revenue_per_all_coreg_views'] = ($graph['all_coreg_revenue'] != 0 && $graph['all_coreg_views'] != 0) ? number_format($graph['all_coreg_revenue'] / $graph['all_coreg_views'], 2) : 0;

        //     //get Coreg P1 Revenue / Views
        //     $graph['coreg_p1_revenue_vs_views'] = ($graph['coreg_p1_revenue'] != 0 && $graph['coreg_p1_views'] != 0) ? number_format($graph['coreg_p1_revenue'] / $graph['coreg_p1_views'], 2) : 0;

        //     //get Coreg P2 Revenue / Views
        //     $graph['coreg_p2_revenue_vs_views'] = ($graph['coreg_p2_revenue'] != 0 && $graph['coreg_p2_views'] != 0) ? number_format($graph['coreg_p2_revenue'] / $graph['coreg_p2_views'], 2) : 0;

        //     //get Coreg P3 Revenue / Views
        //     $graph['coreg_p3_revenue_vs_views'] = ($graph['coreg_p3_revenue'] != 0 && $graph['coreg_p3_views'] != 0) ? number_format($graph['coreg_p3_revenue'] / $graph['coreg_p3_views'], 2) : 0;

        //     //get Coreg P4 Revenue / Views
        //     $graph['coreg_p4_revenue_vs_views'] = ($graph['coreg_p4_revenue'] != 0 && $graph['coreg_p4_views'] != 0) ? number_format($graph['coreg_p4_revenue'] / $graph['coreg_p4_views'], 2) : 0;

        //     //get All Mid Path Revenue / Views
        //     $graph['mp_per_views'] = ($graph['all_mp_revenue'] != 0 && $graph['all_mp_views'] != 0) ? number_format($graph['all_mp_revenue'] / $graph['all_mp_views'], 2) : 0;

        //     //get Permission Data Revenue / Views
        //     $graph['pd_revenue_vs_views'] = ($graph['pd_revenue'] != 0 && $graph['pd_views'] != 0) ? number_format($graph['pd_revenue'] / $graph['pd_views'], 2) : 0;

        //     //get Tiburon Revenue / Views
        //     $graph['tb_revenue_vs_views'] = number_format(($graph['tb_revenue'] != 0 && $graph['tb_views'] != 0) ? $graph['tb_revenue'] / $graph['tb_views'] : 0, 2);

        //     //get Ifficent Revenue / Views
        //     $graph['iff_revenue_vs_views'] = number_format(($graph['iff_revenue'] != 0 && $graph['iff_views'] != 0) ? $graph['iff_revenue'] / $graph['iff_views'] : 0, 2);

        //     //get Rexadz Revenue / Views
        //     $graph['rexadz_revenue_vs_views'] = number_format(($graph['rexadz_revenue'] != 0 && $graph['rexadz_views'] != 0) ? $graph['rexadz_revenue'] / $graph['rexadz_views'] : 0, 2);

        //     //get CPA Revenue / Views
        //     $graph['cpa_revenue_per_views'] = number_format(($graph['cpa_views'] != 0 && $graph['cpa_revenue'] != 0) ? $graph['cpa_revenue'] / $graph['cpa_views'] : 0, 2);

        //     //get Last Page Revenue / Views
        //     $graph['lsp_revenue_vs_views'] = number_format(($graph['lsp_revenue'] != 0 && $graph['lsp_views'] != 0) ? $graph['lsp_revenue'] / $graph['lsp_views'] : 0, 2);

        //     //get CPA / Survey Taker
        //     $graph['cpa_per_survey_takers'] = $graph['cpa_revenue'] != 0 && $graph['survey_takers'] != 0 ? number_format($graph['cpa_revenue'] / $graph['survey_takers'], 2) : 0;

        //     $records[] = $graph;
        // }
        //     // printR($this->records);
        //     // exit;
        // $this->records = $records;
    }

    /**
     * Predefine dates Query for yesterday
     */
    protected function queryYesterday(Builder $query): Builder
    {
        return $query->whereDate('created_at', '=', $this->carbon->yesterday());
    }

    /**
     * Predefine dates Query for week to date
     */
    protected function queryWeekToDate(Builder $query): Builder
    {
        // return $query->whereBetween('created_at', [$this->carbon->subWeek(), $this->carbon->now()]);
        return $query->whereBetween('created_at', [$this->carbon->startOfWeek()->toDateTimeString(), $this->carbon->endOfWeek()->toDateTimeString()]);
    }

    /**
     * Predefine dates Query for month to date
     */
    protected function queryMonthToDate(Builder $query): Builder
    {
        // return $query->whereBetween('created_at', [$this->carbon->subMonth(), $this->carbon->now()]);
        return $query->whereBetween('created_at', [$this->carbon->startOfMonth()->toDateTimeString(), $this->carbon->endOfMonth()->toDateTimeString()]);
    }

    /**
     * Predefine dates Query for all last month
     */
    protected function queryLastMonth(Builder $query): Builder
    {
        $lastMonth = $this->carbon->subMonth();

        return $query->whereMonth('created_at', '=', $lastMonth->month)
            ->whereYear('created_at', '=', $lastMonth->year);
    }

    /**
     *  Set the chart series.
     *  1. Loop through date range/x-axis of Chart.
     *  2. loop through legends for the legends were the column of chart.
     *  3. Fetch colors for colmn.
     *  4. Group the date/categories per slide.
     */
    public function setSeriesThenCategories()
    {

    }

    public function setSubIDsInclude($inputs)
    {
        $this->subid_includes = [
            's1' => $inputs['s1'],
            's2' => $inputs['s2'],
            's3' => $inputs['s3'],
            's4' => $inputs['s4'],
        ];
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getColumnSumQry()
    {
        $selectQry = 'consolidated_graph.*';
        if (count($this->columns)) {
            // $additional = ['coreg_p1_revenue', 'coreg_p1_views','coreg_p2_revenue', 'coreg_p2_views','coreg_p3_revenue', 'coreg_p3_views','coreg_p4_revenue', 'coreg_p4_views', 'all_coreg_views', 'all_mp_views', 'pd_views', 'tb_views', 'iff_views', 'rexadz_views', 'cpa_views', 'lsp_views'];
            // $columns = array_merge($this->columns, $additional);
            $s = [];
            foreach ($this->columns as $c) {
                // if(in_array($c,['coreg_p1_revenue_vs_views', 'coreg_p2_revenue_vs_views', 'coreg_p3_revenue_vs_views', 'coreg_p4_revenue_vs_views', 'all_coreg_revenue_per_all_coreg_views', 'mp_per_views', 'pd_revenue_vs_views', 'tb_revenue_vs_views', 'iff_revenue_vs_views', 'rexadz_revenue_vs_views', 'cpa_revenue_per_views', 'lsp_revenue_vs_views'])) $pro = 'AVG';
                // else $pro = 'SUM';
                // $s[] = "$pro($c) as $c";
                if ($c == 'cost_per_all_clicks') {
                    $qry = '(SUM(cost) / SUM(all_clicks))';
                } elseif ($c == 'survey_takers_per_clicks') {
                    $qry = '(SUM(survey_takers) / SUM(all_clicks))';
                } elseif ($c == 'margin') {
                    $qry = '((SUM(source_revenue) - SUM(cost)) / SUM(source_revenue))';
                } elseif ($c == 'source_revenue_per_all_clicks') {
                    $qry = '(SUM(source_revenue) / SUM(all_clicks))';
                } elseif ($c == 'source_revenue_per_survey_takers') {
                    $qry = '(SUM(source_revenue) / SUM(survey_takers))';
                } elseif ($c == 'all_coreg_revenue_per_all_coreg_views') {
                    $qry = '(SUM(all_coreg_revenue) / SUM(all_coreg_views))';
                } elseif ($c == 'coreg_p1_revenue_vs_views') {
                    $qry = '(SUM(coreg_p1_revenue) / SUM(coreg_p1_views))';
                } elseif ($c == 'coreg_p2_revenue_vs_views') {
                    $qry = '(SUM(coreg_p2_revenue) / SUM(coreg_p2_views))';
                } elseif ($c == 'coreg_p3_revenue_vs_views') {
                    $qry = '(SUM(coreg_p3_revenue) / SUM(coreg_p3_views))';
                } elseif ($c == 'coreg_p4_revenue_vs_views') {
                    $qry = '(SUM(coreg_p4_revenue) / SUM(coreg_p4_views))';
                } elseif ($c == 'mp_per_views') {
                    $qry = '(SUM(all_mp_revenue) / SUM(all_mp_views))';
                } elseif ($c == 'pd_revenue_vs_views') {
                    $qry = '(SUM(pd_revenue) / SUM(pd_views))';
                } elseif ($c == 'tb_revenue_vs_views') {
                    $qry = '(SUM(tb_revenue) / SUM(tb_views))';
                } elseif ($c == 'iff_revenue_vs_views') {
                    $qry = '(SUM(iff_revenue) / SUM(iff_views))';
                } elseif ($c == 'rexadz_revenue_vs_views') {
                    $qry = '(SUM(rexadz_revenue) / SUM(rexadz_views))';
                } elseif ($c == 'adsmith_revenue_vs_views') {
                    $qry = '(SUM(adsmith_revenue) / SUM(adsmith_views))';
                } elseif ($c == 'cpa_revenue_per_views') {
                    $qry = '(SUM(cpa_revenue) / SUM(cpa_views))';
                } elseif ($c == 'lsp_revenue_vs_views') {
                    $qry = '(SUM(lsp_revenue) / SUM(lsp_views))';
                } elseif ($c == 'cpa_per_survey_takers') {
                    $qry = '(SUM(cpa_revenue) / SUM(survey_takers))';
                } elseif ($c == 'all_inbox_per_survey_takers') {
                    $qry = '(SUM(all_inbox_revenue) / SUM(survey_takers))';
                } elseif ($c == 'push_cpa_revenue_per_survey_takers') {
                    $qry = '(SUM(push_revenue) / SUM(survey_takers))';
                } else {
                    $qry = "SUM($c)";
                }
                $s[] = $qry." as $c";
            }

            $s[] = 'DATE_FORMAT(created_at, "%Y-%m-%d") as date';
            $s[] = 'revenue_tracker_id';
            $s[] = 'id';
            $s[] = 's1';
            $s[] = 's2';
            $s[] = 's3';
            $s[] = 's4';
            $s[] = 's5';

            $selectQry = implode(', ', $s);
        }

        // \Log::info($selectQry);
        return $selectQry;
    }
}
