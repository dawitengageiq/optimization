<?php

namespace App\Http\Services\Campaigns;

class ListsApiMultiplePage extends Lists implements \App\Http\Services\Contracts\CampaignListContract
{
    /*
     * Default variables
     *
     */
    protected $uniCap;

    protected $affCap;

    protected $limit;

    protected $filter;

    protected $noTracker;

    protected $creatives;

    protected $campaignTypeOrder;

    protected $repo;

    /**
     * excluded campaign id container
     */
    protected $excludedCampaignIds = [];

    /**
     * Initialize
     */
    public function __construct(
        Utils\Lists\Contracts\StackContract $stack,
        Utils\Lists\NoTracker $noTracker,
        Utils\Lists\Creatives $creatives,
        Utils\Lists\CustomFilter $filter,
        Utils\Lists\Caping\Campaign $uniCap,
        Utils\Lists\Caping\Affilate $affCap,
        Utils\Lists\Limit\FirstLevel\ByRevenueTracker $revenueTrackerLimit,
        Repos\CampaignList $repo
    ) {
        $this->stacking = $stack;
        $this->uniCap = $uniCap;
        $this->affCap = $affCap;
        $this->filter = $filter;
        $this->noTracker = $noTracker;
        $this->creatives = $creatives;
        $this->revenueTrackerLimit = $revenueTrackerLimit;
        $this->repo = $repo;
    }

    /**
     * Set the campaign that will be excluded
     */
    public function setfirstLevelLimit($limit)
    {
        $this->revenueTrackerLimit->set($limit);
    }

    /**
     * Set the campaign that will be excluded
     *
     * @param  array  $campaign_type
     *
     * @var array
     */
    public function setExcludedCampaignIds($campaignIDs)
    {
        $this->excludedCampaignIds = $campaignIDs;
    }

    /**
     * Set the campaign type order, will be used in campaign query
     *
     * @param  array  $campaignType
     *
     * @var array
     */
    public function setTypeOrdering($typeOrdering)
    {
        $this->campaignType = $typeOrdering;
        $this->stacking->setTypeOrdering($typeOrdering);
    }

    /**
     * Query campaigns with relationship
     *
     * @param  int  $affiliateID;
     */
    public function getCampaigns($affiliateID)
    {
        $campaigns = $this->repo->setParams([
            'select' => '',
            'status' => '',
            'exclude_campaignIDs' => $this->excludedCampaignIds,
            'in_campaign_type' => $this->campaignType,
            'with_affiliate_campaign' => $affiliateID,
            // 'with_no_tracker' => $this->userDetails['email'],
            'with_filter_groups' => '',
            'with_creatives' => '',
            'with_config' => '',
            'order_by' => ['priority', 'ASC'],
        ])->get();

        $this->campaigns = ($campaigns) ? $campaigns : [];
    }

    /**
     * Create qery string, This function is copied from PFR before getting campaign content
     */
    public function buildQueryString(int $pathType): array
    {
        return array_values($this->stacking->get($pathType)
            ->map(function ($stackCampaigns) {
                // Empty this variable every time campaign batch looping
                $this->creative = '';

                // Determine if campiagn creatives is available
                // If not available, bypass creatives query string
                if (count($this->creatives())) {
                    // Go through each campaign creatives
                    collect($stackCampaigns)->each(function ($campaignID) {
                        // Determine the campaign id exist on campaign creatives
                        // Then create qury string of creatives for this batch
                        if (array_key_exists($campaignID, $this->creatives())) {
                            // Concat it to accomodate multiple creatives
                            $this->creative .= '&'.http_build_query(
                                ['creatives' => [$campaignID => $this->creatives()[$campaignID]]]
                            );

                        }
                    });
                }

                // Return the query string of campaigns by batch, add the creatives if available
                return http_build_query(['campaigns' => $stackCampaigns]).$this->creative;
            })
            // convert collection to array
            ->toArray());
    }
}
