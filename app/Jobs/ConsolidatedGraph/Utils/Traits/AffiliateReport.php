<?php

namespace App\Jobs\ConsolidatedGraph\Utils\Traits;

trait AffiliateReport
{
    /**
     * Process affiliate report data, affilate report have the revenue data.
     *
     * @param  \Illuminate\Database\Eloquent\Collection|Empty  $affiliateReport
     */
    protected function processAffiliateReport($affiliateReport, $campaigns): void
    {
        if (! $affiliateReport instanceof \Illuminate\Database\Eloquent\Collection) {
            return;
        }

        $this->affiliateReports = $affiliateReport->keyBy('campaign_id')->toArray();

        $coreg1Revenue = 0;
        $coreg2Revenue = 0;
        $coreg3Revenue = 0;
        $coreg4Revenue = 0;

        foreach ($this->affiliateReports as $report) {
            if (in_array($report['campaign_id'], $campaigns[1])) {
                $coreg1Revenue += $report['revenue'];
            }
            if (in_array($report['campaign_id'], $campaigns[2])) {
                $coreg2Revenue += $report['revenue'];
            }
            if (in_array($report['campaign_id'], $campaigns[8])) {
                $coreg3Revenue += $report['revenue'];
            }
            if (in_array($report['campaign_id'], $campaigns[13])) {
                $coreg4Revenue += $report['revenue'];
            }
        }

        $this->setRevenue('coreg_p1_revenue', $coreg1Revenue, false);
        $this->setRevenue('coreg_p2_revenue', $coreg2Revenue, false);
        $this->setRevenue('coreg_p3_revenue', $coreg3Revenue, false);
        $this->setRevenue('coreg_p4_revenue', $coreg4Revenue, false);

        if (! array_key_exists('lsp_revenue', $this->revenues)) {
            $this->revenues['lsp_revenue'] = 0;
        }

        $this->setRevenue('source_revenue', $affiliateReport->sum('revenue'), false);
        $this->setRevenue('cpa_revenue', env('CPA_WALL_ENGAGEIQ_CAMPAIGN_ID', 94), true, $this->revenues['lsp_revenue']);
        $this->setRevenue('pd_revenue', env('EXTERNAL_PATH_PERMISSION_DATA_CAMPAIGN_I', 283), true);
        $this->setRevenue('tb_revenue', env('EXTERNAL_PATH_TIBURON_CAMPAIGN_ID', 290), true);
        $this->setRevenue('iff_revenue', env('EXTERNAL_PATH_IFFICIENT_CAMPAIGN_ID', 287), true);
        $this->setRevenue('rexadz_revenue', env('EXTERNAL_PATH_REXADS_CAMPAIGN_ID', 289), true);
        $this->setRevenue('push_revenue', env('PUSH_CREW_NOTIFICATIONS_CAMPAIGN_ID', 733), true);

    }
}
