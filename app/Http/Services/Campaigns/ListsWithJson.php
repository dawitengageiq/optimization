<?php

namespace App\Http\Services\Campaigns;

use App\Campaign;
use App\FilterType;
use App\Lead;
use CampaignSettings;
use DB;

class ListsWithJson
{
    /*
     * Default variables
     *
     */
    protected $uniCap;

    protected $affCap;

    protected $limit;

    protected $filter;

    protected $campaignTypeOrder;

    protected $campaigns = [];

    protected $userDetails = [];

    protected $typeOrdering = [];

    protected $passedCampaigns = [];

    public $stacking;

    /**
     * Initialize
     */
    public function __construct(
        Utils\Lists\CustomFilter $filter,
        Utils\Lists\Caping\Campaign $uniCap,
        Utils\Lists\Caping\Affilate $affCap,
        Utils\Lists\Stacking\ByPriorityWithJson $stack
    ) {
        $this->filter = $filter;
        $this->uniCap = $uniCap;
        $this->affCap = $affCap;
        $this->stacking = $stack;
    }

    /**
     * Set the campaign type order, will be used in campaign query
     *
     *
     * @var array
     */
    public function setTypeOrder(array $typeOrdering)
    {
        $this->typeOrdering = $typeOrdering;
    }

    /**
     * Query campaigns with relationship
     *
     * @param  int  $revenueTrackerID;
     */
    public function queryCampaigns($revenueTrackerID)
    {
        $campaigns = Campaign::select(
            'id', 'name', 'advertiser_id', 'status', 'lead_cap_type', 'lead_cap_value', 'default_received', 'default_payout', 'priority', 'campaign_type', 'linkout_offer_id',
            //Get lead counts when campaign type is 5
            DB::raw('(CASE WHEN campaign_type = 5 THEN (SELECT count FROM link_out_counts WHERE campaign_id = campaigns.id AND affiliate_id IS NULL LIMIT 1) ELSE (SELECT count FROM lead_counts WHERE campaign_id = campaigns.id AND affiliate_id IS NULL LIMIT 1) END ) AS
                lead_count')
        )
            ->where('status', '!=', 0)
            ->where('status', '!=', 3)
            ->whereIn('campaign_type', $this->typeOrdering)
            ->with(['firstAffiliateCampaign' => function ($q) use ($revenueTrackerID) {
                $q->select('campaign_id', 'lead_cap_type', 'lead_cap_value')
                    ->where('affiliate_id', $revenueTrackerID)
                    ->with(['linkOutCount' => function ($q) use ($revenueTrackerID) {
                        $q->select('campaign_id', 'count')
                            ->where('affiliate_id', $revenueTrackerID);
                    }])
                    ->with(['leadCount' => function ($q) use ($revenueTrackerID) {
                        $q->select('campaign_id', 'count')
                            ->where('affiliate_id', $revenueTrackerID);
                    }]);
            }])
            ->with(['filter_groups' => function ($q) {
                $q->select('id', 'campaign_id', 'status')
                    // Status should be active
                    ->where('status', 1)
                    // Retrieve filters if filter group status is active
                    ->with('filters');
            }])
            ->with(['config' => function ($q) {
                $q->select('id', 'ping_url', 'ping_success');
            }])
            ->orderBy('priority', 'ASC')
            ->get();

        $this->campaigns = ($campaigns) ? $campaigns : [];
    }

    /**
     * Get the  Qualified Campaigns
     *
     * @param  bolean  $filter
     * @param  int  $pathType
     *
     * @var array
     * @var array
     */
    public function filterEachCampaign($toFilter, int $revenueTrackerID)
    {
        /* GO THROUGH EACH CAMPAIGN TO CHECK IF THEY QUALIFY */
        foreach ($this->campaigns as $campaign) {
            // Filter if true.
            // // skip if false.
            if (($toFilter && $toFilter != 'false') || $toFilter == 'true') {
                // If didnt pass the filtering then skip.
                if ($this->filterPassed($campaign, $revenueTrackerID) == false) {
                    continue;
                }
            }

            /* STACK CAMPAIGN AND CREATIVES AND ORDER BY CAMPAIGN TYPE ORDERING */
            $this->stacking->insertIntoStack($campaign);
        }

        return $this->stacking->lists;
    }

    /**
     * Check campaign passed the filtering
     *
     * @param eloquent collection $campaign
     */
    protected function filterPassed($campaign, $revenueTrackerID): bool
    {
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
        // This filter if have user details
        //If didn't passed the filter, skip campaign
        // if($this->filter->passed(
        //     $campaign, $this->userDetails,
        //     CampaignSettings::filterProcessStatus(),
        //     FilterType::select('id','type','name') ->get()->keyBy('id')
        // ) == false) return false;

        return true;
    }
}
