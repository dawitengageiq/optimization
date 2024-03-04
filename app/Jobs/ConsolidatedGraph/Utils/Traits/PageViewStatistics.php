<?php

namespace App\Jobs\ConsolidatedGraph\Utils\Traits;

trait PageViewStatistics
{
    /**
     * Process pa age view statistics
     *
     * @param  \Illuminate\Database\Eloquent\PageViewStatistics|Empty  $pageViewStats
     */
    protected function processPageViewStats($pageViewStats): void
    {
        if (! $pageViewStats instanceof \App\PageViewStatistics) {
            return;
        }

        collect([
            'exitpage' => 'exit_page_views',
            'cpawall' => 'cpa_views',
            'pd' => 'pd_views',
            'tbr1' => 'tb1_views',
            'tbr2' => 'tb2_views',
            'iff' => 'iff_views',
            'rex' => 'rexadz_views',
            'mo1' => 'coreg_p1_views',
            'mo2' => 'coreg_p2_views',
            'mo3' => 'coreg_p3_views',
            'mo4' => 'coreg_p4_views',
        ])
            ->map(function ($field, $indx) use ($pageViewStats) {
                if ($pageViewStats->$field) {
                    $this->pageViewStats[$indx] = $pageViewStats->$field;
                }
            });

    }
}
