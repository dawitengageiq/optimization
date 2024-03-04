<?php

namespace App\Jobs\ConsolidatedGraph\Utils\Traits;

use App\CakeRevenue;

trait CakeRevenue
{
    /**
     * Get the last page revenue, use bench mark to determine the campaign id for last page campaign type
     *
     * @param  \App\CakeRevenue|Empty  $cakeRevenue
     */
    protected function processCakeRevenue($cakeRevenue, $revenueTrackerdID, $exitPageID, $date): void
    {
        // Fetch last page revenue, we will not use the default last page offer id
        if ($exitPageID) {
            $cakeRevenue = $this->lastPageRevenue($revenueTrackerdID, $exitPageID, $date);
        }

        if (! $cakeRevenue instanceof \App\CakeRevenue) {
            return;
        }

        if ($cakeRevenue->revenue) {
            $this->setRevenue('lsp_revenue', $cakeRevenue->revenue, false);
        }

    }

    /**
     * Fetch last page revenue
     */
    public function lastPageRevenue(int $revenueTrackerdID, int $exitPageID, string $date): CakeRevenue
    {
        return \App\CakeRevenue::where('offer_id', $exitPageID)
            ->where('revenue_tracker_id', $revenueTrackerdID)
            ->whereDate('created_at', '=', $date)
            ->get([
                'affiliate_id',
                'revenue_tracker_id',
                'offer_id',
                'revenue',
                'created_at',
            ]);
    }
}
