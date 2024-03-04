<?php

namespace App\Http\Services\Campaigns;

class Lists extends Factories\ListsFactory implements \App\Http\Services\Contracts\CampaignListContract
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

    public $stacking;

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
     * Set the campaign type order, will be used in campaign query
     *
     *
     * @var array
     */
    public function setTypeOrdering(array $typeOrdering)
    {
        $this->typeOrdering = $typeOrdering;
        $this->stacking->setTypeOrdering($typeOrdering);
    }

    /**
     * Query campaigns with relationship
     *
     * @param  int  $revenueTrackerID;
     */
    public function getCampaigns($revenueTrackerID)
    {
        $campaigns = $this->repo->setParams([
            'select' => '',
            'status' => '',
            'in_campaign_type' => $this->typeOrdering,
            'with_affiliate_campaign' => $revenueTrackerID,
            // 'with_no_tracker' => $this->userDetails['email'],
            'with_filter_groups' => '',
            'with_creatives' => '',
            'with_config' => '',
            'order_by' => ['priority', 'ASC'],
        ])->get();

        $this->campaigns = ($campaigns) ? $campaigns : [];
    }

    /**
     * Pluck only campaign ids into array
     */
    public function creatives()
    {
        return $this->creatives->get();
    }
}
