<?php

namespace App\Http\Services\Campaigns;

class ListsApiOnePage extends Factories\ListsFactory implements \App\Http\Services\Contracts\CampaignListContract
{
    /**
     * Injected class
     */
    protected $uniCap;

    protected $affCap;

    protected $limit;

    protected $filter;

    protected $noTracker;

    protected $creatives;

    protected $campaignContent;

    protected $campaignTypeOrder;

    protected $repo;

    /**
     * excluded campaign id container
     */
    protected $excludedCampaignIds = [];

    /**
     * Initialize
     *
     * @var object
     * @var object
     * @var object
     * @var object
     * @var object
     * @var object
     * @var object
     */
    public function __construct(
        Utils\Lists\Contracts\StackContract $stack,
        Utils\Lists\NoTracker $noTracker,
        Utils\Lists\Creatives $creatives,
        Utils\Lists\CustomFilter $filter,
        Utils\Lists\Caping\Campaign $uniCap,
        Utils\Lists\Caping\Affilate $affCap,
        Utils\Lists\Limit\FirstLevel\ByRevenueTracker $revenueTrackerLimit,
        Content $campaignContent,
        Repos\CampaignList $repo
    ) {
        $this->stacking = $stack;
        $this->uniCap = $uniCap;
        $this->affCap = $affCap;
        $this->filter = $filter;
        $this->noTracker = $noTracker;
        $this->creatives = $creatives;
        $this->revenueTrackerLimit = $revenueTrackerLimit;
        $this->campaignContent = $campaignContent;
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
     * Get the qualified campaigns
     */
    public function getCampaignsContent(
        array $userDetails,
        int $limit,
        array $qualifiedCampaigns): array
    {
        // Set the campaigns
        $this->campaignContent->setCampaigns($qualifiedCampaigns);
        // set affiliate id
        $this->campaignContent->setAffiliateID($userDetails['affiliate_id']);
        // Process only campaigns that exists
        if ($this->campaignContent->hasCampaigns() && $this->campaignContent->hasCampaignType()) {
            // Set the campaign creatives
            $this->campaignContent->creativeContent->setCreativeIDS($this->creatives->get());
            // Get the stack content of each campaign
            // Process campaigns with given limit
            $this->campaignContent->process(
                $userDetails,
                $limit);

            /* SUBMIT BUTTON */
            // Add submit button at the bottom
            $this->campaignContent->addButton($userDetails['submitBtn']);

            return $this->campaignContent->getHtmlData();
        }

        return ['message' => trans('campaignList.dont_exist')];
    }
}
