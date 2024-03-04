<?php

namespace App\Jobs\Reordering;

use Carbon\Carbon;
use Log;

class DailyMixCoreg
{
    protected $debug = false;

    /**
     * Inintialization,
     * gather the dependent classes
     * and set the current time.
     *
     * @method __construct
     */
    public function __construct(
        \App\Helpers\Repositories\Settings $settings,
        Helpers\Leads $leads,
        Helpers\RevenueTrackers $revenueTrackers,
        Helpers\MixedCoregCampaignOrder $campaignOrder,
        Helpers\CampaignViewReports $campaignViewReports,
        Utils\Calculate $calculate,
        Utils\Ordering $ordering
    ) {
        $this->settings = $settings;
        $this->leads = $leads;
        $this->revenueTrackers = $revenueTrackers;
        $this->campaignOrder = $campaignOrder;
        $this->campaignViewReports = $campaignViewReports;
        $this->calculate = $calculate;
        $this->ordering = $ordering;

        $now = Carbon::now();
        // Use to query leads between $now and the revenue reference date.
        $this->leads->setCurrentDate($now->toDateTimeString());
        // Use to update the reference date after saving.
        $this->campaignOrder->setCurrentDate($now->toDateTimeString());
        // Use to query revenue with schedule task of this moment.
        // Uncomment below if the schedule was set to every minute or hourly.
        if (! $this->debug) {
            $this->revenueTrackers->setTime($now->hour);
        }
    }

    /**
     * Set the revenue tracker id that will be fetch.
     * If the schedule was set daily, needs to supply the revenue tracker ids inorder to exactly fetch the right revenue tracker
     * If the schedule was set to every minute or hourly, this functions is not needed.
     *
     * @method setRevenueTRrackerID
     */
    public function setRevenueTRrackerID(int $revenueTRrackerID)
    {
        $this->revenueTrackers->setRevenueTRrackerID($revenueTRrackerID);
    }

    /**
     * Execute the ordering
     */
    public function execute(): void
    {
        /** STEP 1 CHECK IF DISABLE OR NOT **/
        // If status on reordring mix coreg is disable, do not continue.
        if ($this->settings->getValue('mixed_coreg_campaign_reordering_status') != 1) {
            return;
        }

        /** STEP 2 GET REVENUE TRACKERS **/
        // Mixed coreg campaign type for ordering.
        $this->revenueTrackers->setMixeCoregTypeIDs(array_keys(config('constants.MIXED_COREG_TYPE_FOR_ORDERING')));
        // Query all affiliate revenue trackers
        $this->revenueTrackers->query();
        // Stop if no records
        if ($this->revenueTrackers->notExists()) {
            return;
        }

        // Go through all revenue trackers by decorators
        foreach ($this->revenueTrackers->getRevenueTracker() as $revenueTracker) {
            // printR($revenueTracker, true);
            // Revenue tracker trait
            $this->revenueTrackers->setTraitsOf($revenueTracker);
            echo 'Revenue Tracker ID:'.$this->revenueTrackers->revenueTrackerID()."\n";

            /** STEP 3 GET LEADS **/
            // Supply the campaign order, campaign ids list that was previously ordered.
            $this->leads->setCampaignOrder($this->revenueTrackers->campaignOrder());
            // Revenue tracker id as leads parameter on query.
            $this->leads->setRevenueTRrackerID($this->revenueTrackers->revenueTrackerID());
            // Supply the previous reference date to be used to query leads for time range of now and reference date.
            if (! $this->debug) {
                $this->leads->setReferenceDAte($this->revenueTrackers->referenceDate());
            }
            // Then query
            $this->leads->query();
            // Skip this revenue tracker if no leads where fetch.
            if ($this->leads->notExists()) {
                continue;
            }

            /** STEP 4 CALCULATE **/
            // Supply the campaign order, campaign ids list that was previously ordered.
            $this->calculate->setCampaignOrder($this->revenueTrackers->campaignOrder());
            // Calculate revenue per views
            $this->calculate->revenuePerViews($this->leads->get());

            /** STEP 5 REORDER **/
            // Supply the campaign ids lists with the caculated revenue from $calculate::class
            $this->ordering->setOrders($this->calculate->getOrders());
            // Reorder the campaign order base on leads revenue per view
            $this->ordering->reorderBy($this->revenueTrackers->mixedCoregOrderBy());

            /** STEP 6 SAVE THE NEW ORDER **/
            // Supply the mixedcoreg campaign order eloquent object to campaignOrder::class.
            $this->campaignOrder->set($this->revenueTrackers->mixedCoregCampaignOrder());
            // Save the new order from the results of reordering of $ordering::class
            $this->campaignOrder->save('['.implode(',', array_keys($this->ordering->getOrders())).']');

            /* Done reordering */
            echo 'newCampaignOrder: '.$this->campaignOrder->campaignIdOrder()."\n";
            Log::info('newCampaignOrder: '.$this->campaignOrder->campaignIdOrder());

            /** STEP 7 RESET VIEW REPORTS **/
            // Supply the campaign view reports eloquent object to campaignViewReports::class.
            $this->campaignViewReports->set($this->revenueTrackers->campaignViewReports());
            // Then reset
            $this->campaignViewReports->reset();
        }

        echo 'Done Executing'."\n";
    }

    /**
     * Skip revenue tracker if threshold views not reach
     */
    public function skipThis(): bool
    {
        $this->revenueTrackerOrderBy = $this->revenueTracker->mixed_coreg_order_by;

        if ($this->revenueTracker->mixed_coreg_campaign_views == 0) {
            $this->revenueTracker->mixed_coreg_campaign_views = $this->settings->getValue('campaign_type_view_count');
        }

        foreach ($this->revenueTracker->campaignViewReports as $campaignViewReport) {
            if (! is_object($campaignViewReport->campaignStatusInfo)) {
                return true;
            }

            if ($campaignViewReport->campaignStatusInfo->status == 1
            && $campaignViewReport->affiliate_campaign_record->count == 0) {
                return true;
            }

            // Question: do we apply the comparison of view count to revenue tracker mixcoreg threshold views on daily ordering?
            if ($campaignViewReport->current_view_count == 0
            || $campaignViewReport->current_view_count < $this->revenueTracker->mixed_coreg_campaign_views) {
                return true;
            }
        }

        return false;
    }
}
