<?php

namespace App\Http\Services\Consolidated\Export2Excel;

use App\ConsolidatedGraph;
use Carbon\Carbon;

/**
 * Consolidate graph class.
 * Extended by GraphByRevenueTrackerID::class
 */
class ByDateRangeMultiple extends \App\Http\Services\Consolidated\GraphByDateRangeMultiple implements \App\Http\Services\Contracts\ConsolidatedGraphContract
{
    /**
     * Get the consilidated data to be use in excel file.
     *
     * @model ConsolidatedGraph.
     */
    public function getConsolidatedData()
    {
        // \Log::info($this->columns);
        // \DB::enableQueryLog();
        // \DB::connection('secondary')->enableQueryLog();
        $selectQry = '*';
        if (count($this->columns)) {
            $select = $this->columns;
            $selectQry = '';
            $s = [];
            foreach ($this->columns as $c) {
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

            $selectQry = implode(', ', $s);
        }

        $query = $this->model
            ->select(
                \DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as date'),
                'revenue_tracker_id',
                $this->subid_includes['s1'] ? 's1' : \DB::RAW('"" as s1'),
                $this->subid_includes['s2'] ? 's2' : \DB::RAW('"" as s2'),
                $this->subid_includes['s3'] ? 's3' : \DB::RAW('"" as s3'),
                $this->subid_includes['s4'] ? 's4' : \DB::RAW('"" as s4'),
                's5',
                \DB::raw($selectQry)
            );

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

        $sumQuery = clone $query;

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

        //RECORDS
        $records = $query
            ->orderBy('revenue_tracker_id', 'ASC')
            ->orderBy('date', 'ASC')
            ->orderBy('s1', 'ASC')
            ->orderBy('s2', 'ASC')
            ->orderBy('s3', 'ASC')
            ->orderBy('s4', 'ASC')
            ->get();
        $this->records = $records;

        //TOTAL PER SUBID SUMMARY
        $sumQuery->groupBy('revenue_tracker_id');
        if ($this->subid_includes['s1']) {
            $sumQuery->groupBy('s1');
        }
        if ($this->subid_includes['s2']) {
            $sumQuery->groupBy('s2');
        }
        if ($this->subid_includes['s3']) {
            $sumQuery->groupBy('s3');
        }
        if ($this->subid_includes['s4']) {
            $sumQuery->groupBy('s4');
        }

        $this->subIDSummaryRecords = $sumQuery->orderBy('revenue_tracker_id', 'ASC')
            ->orderBy('s1', 'ASC')
            ->orderBy('s2', 'ASC')
            ->orderBy('s3', 'ASC')
            ->orderBy('s4', 'ASC')
            ->get();
        // \Log::info($this->subIDSummaryRecords);

        //TOTALS PER SUBID
        $addQuery = [
            // 'SUM(cost) as cost',
            // 'SUM(all_clicks) as all_clicks',
            // 'SUM(survey_takers) as survey_takers',
            // 'SUM(source_revenue) as source_revenue',
            // 'SUM(all_coreg_revenue) as all_coreg_revenue',
            'SUM(all_coreg_views) as all_coreg_views',
            'SUM(coreg_p1_revenue) as coreg_p1_revenue',
            'SUM(coreg_p1_views) as coreg_p1_views',
            'SUM(coreg_p2_revenue) as coreg_p2_revenue',
            'SUM(coreg_p2_views) as coreg_p2_views',
            'SUM(coreg_p3_revenue) as coreg_p3_revenue',
            'SUM(coreg_p3_views) as coreg_p3_views',
            'SUM(coreg_p4_revenue) as coreg_p4_revenue',
            'SUM(coreg_p4_views) as coreg_p4_views',
            // 'SUM(all_mp_revenue) as all_mp_revenue',
            'SUM(all_mp_views) as all_mp_views',
            // 'SUM(pd_revenue) as pd_revenue',
            'SUM(pd_views) as pd_views',
            // 'SUM(tb_revenue) as tb_revenue',
            'SUM(tb_views) as tb_views',
            // 'SUM(iff_revenue) as iff_revenue',
            'SUM(iff_views) as iff_views',
            // 'SUM(rexadz_revenue) as rexadz_revenue',
            'SUM(rexadz_views) as rexadz_views',
            // 'SUM(adsmith_revenue) as adsmith_revenue',
            // 'SUM(adsmith_views) as adsmith_views',
            // 'SUM(cpa_revenue) as cpa_revenue',
            'SUM(cpa_views) as cpa_views',
            // 'SUM(lsp_revenue) as lsp_revenue',
            'SUM(lsp_views) as lsp_views',
            // 'SUM(all_inbox_revenue) as all_inbox_revenue',
            // 'SUM(push_revenue) as push_revenue'
        ];

        $perQuery = $this->model
            ->select(
                \DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as date'),
                'revenue_tracker_id',
                $this->subid_includes['s1'] ? 's1' : \DB::RAW('"" as s1'),
                $this->subid_includes['s2'] ? 's2' : \DB::RAW('"" as s2'),
                $this->subid_includes['s3'] ? 's3' : \DB::RAW('"" as s3'),
                $this->subid_includes['s4'] ? 's4' : \DB::RAW('"" as s4'),
                's5',
                \DB::raw($selectQry),
                \DB::raw(implode(', ', $addQuery))
            );

        if (count($this->revenueTrackerIDs)) {
            $perQuery->whereIn('revenue_tracker_id', $this->revenueTrackerIDs);
        }

        // Date range by date picker
        if (! $this->predefineDates) {
            $this->dateFrom = Carbon::parse($this->dateFrom)->startOfDay();
            $this->dateTo = Carbon::parse($this->dateTo)->endOfDay();
            $perQuery->whereBetween('created_at', [$this->dateFrom, $this->dateTo]);
        }
        // Date range by pre define dates
        else {
            $perQuery = $this->{'query'.ucwords(camelCase($this->predefineDates))}($perQuery);
        }

        $perQuery->groupBy('date');
        $perQuery->groupBy('revenue_tracker_id');
        if ($this->subid_includes['s1']) {
            $perQuery->groupBy('s1');
        }
        if ($this->subid_includes['s2']) {
            $perQuery->groupBy('s2');
        }
        if ($this->subid_includes['s3']) {
            $perQuery->groupBy('s3');
        }
        if ($this->subid_includes['s4']) {
            $perQuery->groupBy('s4');
        }

        $this->perSubIDRecords = $perQuery
            ->orderBy('revenue_tracker_id', 'ASC')
            ->orderBy('s1', 'ASC')
            ->orderBy('s2', 'ASC')
            ->orderBy('s3', 'ASC')
            ->orderBy('s4', 'ASC')
            ->orderBy('date', 'ASC')
            ->get();
        $this->perSubIDRecords;

        // \Log::info(\DB::getQueryLog());
        // \Log::info(\DB::connection('secondary')->getQueryLog());
    }

    public static function stringifyZero(&$item, $key)
    {
        $keys = ['revenue_tracker_id', 's1', 's2', 's3', 's4', 's5'];
        if (! in_array($key, $keys) && (trim($item) == '' || $item == null)) {
            $item = 0;
        }
        $item = is_numeric($item) && ! in_array($key, $keys) ? (float) sprintf('%0.2f', $item) : (string) $item;

        // \Log::info($key.' - '. $item);
    }
}
