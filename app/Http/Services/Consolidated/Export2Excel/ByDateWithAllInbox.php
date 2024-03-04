<?php

namespace App\Http\Services\Consolidated\Export2Excel;

use App\ConsolidatedGraph;

/**
 * Consolidate graph class.
 * Extended by GraphByRevenueTrackerID::class
 */
class ByDateWithAllInbox extends \App\Http\Services\Consolidated\GraphAllInbox implements \App\Http\Services\Contracts\ConsolidatedGraphContract
{
    /**
     * Get the consilidated data to be use in excel file.
     *
     * @model ConsolidatedGraph.
     */
    public function getConsolidatedData()
    {
        $this->records = $this->model
            ->select(
                \DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as date'),
                'survey_takers',
                'source_revenue',
                'source_revenue_per_survey_takers',
                'source_revenue_per_survey_takers',
                'all_clicks',
                'source_revenue_per_all_clicks',
                'margin',
                'cpa_revenue',
                'cpa_revenue_per_views',
                'lsp_revenue',
                'lsp_views',
                'lsp_revenue_vs_views',
                'pd_revenue',
                'pd_revenue_vs_views',
                'tb_revenue',
                'tb_revenue_vs_views',
                'iff_revenue',
                'iff_revenue_vs_views',
                'rexadz_revenue',
                'rexadz_revenue_vs_views',
                'all_inbox_revenue',
                'coreg_p1_revenue',
                'coreg_p1_revenue_vs_views',
                'coreg_p2_revenue',
                'coreg_p2_revenue_vs_views',
                'survey_takers_per_clicks',
                'cpa_per_survey_takers',
                'mp_per_views'
            )
            ->where('revenue_tracker_id', $this->revenueTrackerID)
            ->whereDate('created_at', '=', $this->date)
            ->get();
    }
}
