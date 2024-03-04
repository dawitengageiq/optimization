<?php

namespace App\Http\Services\Campaigns\Factories;

use App\CakeConversion;
use App\FilterType;
use App\Lead;
use RevenueTracker;

/*
|--------------------------------------------------------------------------
| Abstraction class for campaign listing
|--------------------------------------------------------------------------
|
| This class is extendable and uses for campaign listing.
| The extended class should inject dependencies for this to work.
| Dependencies are:
| - Utils\Lists\Contracts\StackContract $stack,
| - Utils\Lists\NoTracker $noTracker,
| - Utils\Lists\Creatives $creatives,
| - Utils\Lists\CampaignFilter $filter,
| - Utils\Lists\Caping\Campaign $uniCap,
| - Utils\Lists\Caping\Affilate $affCap,
| - Utils\Lists\Limit\FirstLevel\ByRevenueTracker $revenueTrackerLimit
|
| The extending classes should also provide the function setCampaignTypeOrder($typeOrdering), what types of campaign should be retrieve and order accordingly.
| The extending classes should also provide the function getCampaigns($affiliateID), to retrieve campaigns details.
|
| Current extending classes:
| - App\Http\Services\Campaigns\Lists
| - App\Http\Services\Campaigns\ListApiOnePage
| - App\Http\Services\Campaigns\ListApiMultiplePage
|
 */
abstract class ListsFactory
{
    /*
     * Default variables
     *
     */
    protected $userDetails;

    protected $cakeClicks;

    protected $filterTypes;

    protected $leadCampaigns;

    protected $campaignTypeOrder;

    protected $filterStatusSettings;

    protected $noLimitSettings;

    protected $campaigns = [];

    protected $typeOrdering = [];

    protected $passedCampaigns = [];

    /**
     * Function required to be provide on extended class
     */
    abstract public function setTypeOrdering(array $typeOrdering);

    abstract public function getCampaigns(array $affiliateID);

    /**
     * Get list of campaigns to Array
     *
     * @return array
     */
    public function all()
    {
        return $this->campaigns->toArray();
    }

    /**
     * Set user details
     *
     * @param  array  $userDetails
     *
     * @var array
     */
    public function setUserDetails($userDetails)
    {
        $this->userDetails = $userDetails;
    }

    /**
     * Set settings.
     *
     * @param  array  $settings
     */
    public function setCampaignNoLimitSettings($noLimitSettings)
    {
        $this->noLimitSettings = $noLimitSettings;
    }

    public function filterProcessStatus($filterStatusSettings)
    {
        $this->filterStatusSettings = $filterStatusSettings;
    }

    /**
     * Get the  Qualified Campaigns
     *
     * @param  bolean  $filter
     * @param  int  $pathType
     * @param  int  $revenueTrackerID
     *
     * @var array
     * @var array
     */
    public function filterCampaigns(
        $filter,
        $pathType,
        $revenueTrackerID)
    {
        /*  */
        $this->filters();

        /* GO THROUGH EACH CAMPAIGN TO CHECK IF THEY QUALIFY */
        foreach ($this->campaigns as $campaign) {
            if (($filter && $filter != 'false') || $filter == 'true') {
                if ($this->filterPassed($campaign, $revenueTrackerID) == false) {
                    continue;
                }
            }

            /* STACK CAMPAIGN AND CREATIVES AND ORDER BY CAMPAIGN TYPE ORDERING */
            //$this->stackCampaign( $campaign, $pathType );
            $this->stacking->stackCampaign($campaign, $pathType);

            // Creatives
            $this->creatives->set($campaign->creatives, $campaign->id);
        }
    }

    /**
     * Get the data needed for filtering
     *
     * @var array
     * @var array
     * @var array
     */
    protected function filters()
    {
        /* FILTER TYPE */
        $this->filterTypes = FilterType::select('id', 'type', 'name')
            ->get()
            ->keyBy('id');

        /* GET ALL LEADS ANSWERED BY EMAIL */
        $this->leadCampaigns = Lead::where('lead_email', $this->userDetails['email'])
            ->whereIn('lead_status', [1, 3, 4])
            ->pluck('campaign_id')
            ->toArray();

        /* GET CAKE CONVERSIONS CLICKED BY EMAIL */
        $this->cakeClicks = CakeConversion::where('sub_id_5', $this->userDetails['email'])
            ->pluck('offer_id')
            ->toArray();
    }

    /**
     * Check campaign passed the filtering
     *
     * @param eloquent collection $campaign
     * @return bool
     */
    protected function filterPassed($campaign, $revenueTrackerID)
    {
        /* CHECK IF USER ALREADY ANSWERED CAMPAIGN */
        if (in_array($campaign->id, $this->leadCampaigns)) {
            return false;
        } //If already answered, skip campaign

        /* CHECK IF CAMPAIGN EXCEEDS NO Click LIMIT */
        // if($this->noTracker->passed($campaign, $this->noLimitSettings) == false)  return false;

        /* CHECK EACH CAMPAIGN CAP IF REACHED */
        //If default campaign cap failed, skip campaign
        if ($this->uniCap->passed($campaign) == false) {
            return false;
        }

        /* CHECK AFFILIATE CAMPAIGN CAP */
        //If affiliate campaign cap failed, skip campaign

        if ($this->affCap->passed($campaign, $revenueTrackerID) == false) {
            return false;
        }

        /* PROCESS CAMPAIGN FILTER */
        //If didn't passed the filter, skip campaign
        if ($this->filter->passed($campaign, $this->userDetails, $this->filterStatusSettings, $this->filterTypes) == false) {
            return false;
        }

        /* PROCESS LINKOUTS CLICK, IFUSER ALREADY ANSWERED CAMPAIGN */
        if ($campaign->campaign_type == 5 && in_array($campaign->linkout_offer_id, $this->cakeClicks)) {
            return false;
        }

        /* PROCESS CAMPAIGN LIMIT PER REVENUETRACKER */
        // First Level Limit.
        //  Affiliate revenue tracker have limt per campaign type.
        //  Exclude mix coreg for first level limit
        //  @source App\Http\Services\Campaigns\Utils\Lists\Limit\FirstLevel\ByRevenueTracker::class
        if ($this->firstLevelLimitIsPassed($campaign->campaign_type) == false) {
            return false;
        }

        return true;
    }

    /**
     * Bypass campaign type coreg for it will be process later
     * Check limitaion for other campaign type
     *
     * @param  int  $campaignType
     * @return bool
     */
    public function firstLevelLimitIsPassed($campaignType)
    {
        if (in_array($campaignType, array_keys(config('constants.MIXED_COREG_TYPE_FOR_ORDERING')))) {
            return true;
        }

        if ($this->revenueTrackerLimit->exceed($campaignType)) {
            return false;
        }

        return true;
    }
}
