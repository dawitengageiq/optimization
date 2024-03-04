<?php

namespace App\Jobs\ConsolidatedGraph\Utils\Traits;

trait Margin
{
    /**
     * Process margin.
     */
    protected function processMargin(): void
    {
        if (! array_key_exists('source_revenue', $this->revenues)) {
            $this->revenues['source_revenue'] = 0;
        }
        if (! array_key_exists('payout', $this->revenues)) {
            $this->revenues['payout'] = 0;
        }

        if (($this->revenues['source_revenue'] == 0)) {
            return;
        }

        $margin = (($this->revenues['source_revenue'] - $this->clicksRegStats['payout']) / $this->revenues['source_revenue']) * 100;

        $this->clicksRegStats['margin'] = number_format($margin, 2, '.', '');
    }
}
