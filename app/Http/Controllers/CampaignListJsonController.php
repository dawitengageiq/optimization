<?php

namespace App\Http\Controllers;

use CampaignSettings;
use Illuminate\Http\Request;
use RevenueTracker;

class CampaignListJsonController extends Controller
{
    /**
     * Injected class
     */
    protected $campaignList;

    /**
     * Load the needed dependencies
     */
    public function __construct(\App\Http\Services\Campaigns\ListsWithJson $campaignList)
    {
        $this->campaignList = $campaignList;
    }

    /**
     * Get campaigns list
     */
    public function getCampaigns(Request $request)
    {
        echo '<h1>GetCampaignsAndJsonContent</h1>';

        // Get the revenue tracker and settings of revenue tracker
        RevenueTracker::defaultRevenueTracker();

        // Set the campaign type to be included in query
        // And the ordering will be used too in stacking
        $this->campaignList->setTypeOrder(json_decode(CampaignSettings::campaignTypeOrder()));

        // Query campaigns
        $this->campaignList->queryCampaigns(RevenueTracker::trackerID());

        /* FILTER CAMPAIGNS */
        $this->qualifiedCampaigns = $this->campaignList->filterEachCampaign(
            // True: Apply filter.
            // False: skip filtering.
            false,
            /* PROVIDE REVENUE TRACKER ID */
            RevenueTracker::trackerID()
        );

        // Go through ordering of campaign type
        $stack = array_values(collect(json_decode(CampaignSettings::campaignTypeOrder()))
            // Go through the stack campaign type order collections
            ->map(function ($type) {
                // Check the campaign type(item) is't in the saved stack
                // if available: return the stack as new item of stack campaign type order collections
                if (array_key_exists($type, $this->qualifiedCampaigns)) {

                    return $this->qualifiedCampaigns[$type];
                }
            })
            // Filter: remove empty indexes in stack campaign type order collections
            ->filter()
            // Since the Array is 3x deep, we need to flatten to one dimensional array
            ->flatMap(function ($campaigns) {
                // Sort the ordering by key
                ksort($campaigns[0]);
                // arrange the keys as incremental from 0
                $campaigns[0] = array_values($campaigns[0]);

                return $campaigns;
            })
            // Filter: remove empty indexes in stack campaign type order collections
            ->filter()
            ->toArray());

        echo '<pre>';
        print_r($stack);
        echo '</pre>';
    }
}
