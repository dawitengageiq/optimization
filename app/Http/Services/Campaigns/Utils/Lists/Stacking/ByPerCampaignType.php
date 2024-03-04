<?php

namespace App\Http\Services\Campaigns\Utils\Lists\Stacking;

class ByPerCampaignType extends ByPriority implements \App\Http\Services\Campaigns\Utils\Lists\Contracts\StackContract
{
    /*
     * Default variables
     *
     */
    protected $ordering;

    /**
     * Initialize, Inject dependencies
     * Type of ordering will be used: mix coreg ordering, campaign type ordering and priority ordering
     */
    public function __construct(
        \App\Http\Services\Campaigns\Utils\Lists\Contracts\LimitContract $limit,
        \App\Http\Services\Campaigns\Utils\Lists\Ordering\ByCampaignType $campaignTypeOrder,
        \App\Http\Services\Campaigns\Utils\Lists\ExitPage $exitPage
    ) {
        $this->limit = $limit;
        $this->exitPage = $exitPage;
        $this->ordering = $campaignTypeOrder;
        $this->campaignTypesName = config('constants.CAMPAIGN_TYPES');
    }

    /**
     * Set the ordeing data and limit data
     * Primary: get the mix coreg order, if not available
     * Order by priority(set in query params)
     * Set limit that was provided in service provider
     */
    public function setOrderAndLimits(array $param)
    {
        [$limit, $revenueTrackerLimit, $revenueTrackerID, $campaignTypeOrder] = $param;

        /* GET CAMPAIGN ORDER PER CAMPAIGN TYPE ORDER */
        $this->ordering->get(
            $revenueTrackerID,
            $campaignTypeOrder
        );

        /* Set limit per campaign type */
        $this->limit->set($limit);
        $this->revenueTrackerLimit = $revenueTrackerLimit;
    }

    /**
     * Check has ordering
     */
    public function hasOrder(): bool
    {
        if ($this->ordering->hasOrder()) {
            $this->orderType = 'Campaign Type';

            return true;
        }

        return false;
    }

    /**
     * Get what type of ordering was used
     */
    public function orderType(): bool
    {
        return $this->orderType;
    }

    /**
     * Stack the qualified campaign coregs
     *
     *
     * @var array
     */
    protected function stackCampaignCoreg(collection $campaign)
    {
        // Pre populate
        if (! array_key_exists($campaign->campaign_type, $this->stack)) {
            $this->stack[$campaign->campaign_type][0] = [];
        }

        //get last array
        $lastSet = count($this->stack[$campaign->campaign_type]) - 1;

        // If the campaign type order has inner ordering (campaign type path ordering).
        if ($this->ordering->has($campaign->campaign_type)) {
            $this->stackByCampaignTypeOrder($campaign->id, $campaign->campaign_type, $lastSet);

            return;
        }

        // Populate
        array_push($this->stack[$campaign->campaign_type][$lastSet], $campaign->id);
    }

    /**
     * Stack the qualified campaign  other coregs and exit page
     *
     *
     * @var array
     */
    protected function stackOtherCampaigns(collection $campaign)
    {
        if ($this->limit->exceed($campaign->campaign_type)) {
            return;
        }

        // Pre populate
        if (! array_key_exists($campaign->campaign_type, $this->stack)) {
            $this->stack[$campaign->campaign_type][0] = [];
        }

        //get last array
        $lastSet = count($this->stack[$campaign->campaign_type]) - 1;

        // If the campaign type order has inner ordering (campaign type path ordering).
        if ($this->ordering->has($campaign->campaign_type)) {
            $this->stackByCampaignTypeOrder($campaign->id, $campaign->campaign_type, $lastSet);

            return;
        }

        // Push campaign id to stack.
        array_push($this->stack[$campaign->campaign_type][$lastSet], $campaign->id);
    }

    /**
     * Implement mix coreg order
     */
    protected function stackByCampaignTypeOrder(int $campaignID, $campaignType, $lastSet)
    {
        if ($this->ordering->campaignIdExists($campaignID)) {
            // Populate
            $this->stack = $this->ordering->stack($campaignID, $campaignType, $lastSet, $this->stack);

            return;
        }
        // push at the end of stack
        $this->stack = $this->ordering->push($campaignID, $campaignType, $lastSet, $this->stack);
    }

    /**
     * Process first level limit
     */
    protected function implementCoregsLimit()
    {
        $this->stack = $this->limit->mixedCoregLimit->apply([
            $this->stack,
            $this->limit->getPathLimit(),
            $this->revenueTrackerLimit,
        ]);
    }
}
