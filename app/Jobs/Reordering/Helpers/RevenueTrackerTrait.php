<?php

namespace App\Jobs\Reordering\Helpers;

trait RevenueTrackerTrait
{
    /**
     * Set individual revenue tracker
     * It will be use to get its own traits
     */
    public function setTraitsOf(eloquentCollection $revenueTracker)
    {
        $this->revenueTracker = $revenueTracker;
    }

    /**
     * Get order of campaign ids
     */
    public function campaignOrder(): array
    {
        return json_decode($this->revenueTracker->mixedCoregCampaignOrder->campaign_id_order);
    }

    /**
     * Get revenue tracker id
     */
    public function revenueTrackerID(): int
    {
        return $this->revenueTracker->revenue_tracker_id;
    }

    /**
     * Get reference date
     *
     * @return string|timestamp
     */
    public function referenceDate()
    {
        return $this->revenueTracker->mixedCoregCampaignOrder->reorder_reference_date;
    }

    /**
     * Get order by
     */
    public function mixedCoregOrderBy(): int
    {
        return $this->revenueTracker->mixed_coreg_order_by;
    }

    /**
     * Get eloquentCollection campaign order
     */
    public function mixedCoregCampaignOrder(): eloquentCollection
    {
        return $this->revenueTracker->mixedCoregCampaignOrder;
    }

    /**
     * Get eloquentCollection campaign view reports
     */
    public function campaignViewReports(): eloquentCollection
    {
        return $this->revenueTracker->campaignViewReports;
    }

    /**
     * check if recurrence is ...
     */
    public function recurrenceIs(string $recurrence): bool
    {
        if ($this->revenueTracker->mixed_coreg_recurrence == $recurrence) {
            return true;
        }

        return false;
    }

    /**
     * check if recurrence is not ...
     */
    public function recurrenceIsNot(string $recurrence): bool
    {
        if ($this->revenueTracker->mixed_coreg_recurrence != $recurrence) {
            return true;
        }

        return false;
    }
}
