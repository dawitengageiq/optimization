<?php

namespace App\Jobs\ConsolidatedGraph\Utils\Traits;

trait ClicksVsRegistrationStatistics
{
    /**
     * Process Clicks per registration statistics.
     * Survey takers count and all clicks count.
     *
     * @param  \App\ClicksVsRegistrationStatistics|Empty  $clicksRegStats
     */
    protected function processClicksRegStats($clicksRegStats): void
    {
        if (! $clicksRegStats instanceof \App\ClicksVsRegistrationStatistics) {
            return;
        }

        if ($clicksRegStats->registration_count) {
            $this->clicksRegStats['survey_takers'] = $clicksRegStats->registration_count;
        }
        if ($clicksRegStats->clicks) {
            $this->clicksRegStats['all_clicks'] = $clicksRegStats->clicks;
        }

    }
}
