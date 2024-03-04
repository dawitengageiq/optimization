<?php

namespace App\Http\Services\Campaigns\Utils\Lists\Stacking;

class ByMixCoregPages extends ByPerCampaignType implements \App\Http\Services\Campaigns\Utils\Lists\Contracts\StackContract
{
    /**
     * Initialize, Inject dependencies
     * Type of ordering will be used: mix coreg ordering, campaign type ordering and priority ordering
     */
    public function __construct(
        \App\Http\Services\Campaigns\Utils\Lists\Contracts\LimitContract $limit,
        \App\Http\Services\Campaigns\Utils\Lists\Ordering\ByMixCoreg $mixCoregOrder,
        // Fallback ordering when mixcoreg ave no ordering
        // If camapign type have no ordering, use priority ordering
        \App\Http\Services\Campaigns\Utils\Lists\Ordering\ByCampaignType $campaignTypeOrder,
        \App\Http\Services\Campaigns\Utils\Lists\ExitPage $exitPage
    ) {
        $this->limit = $limit;
        $this->exitPage = $exitPage;
        $this->mixCoregOrder = $mixCoregOrder;
        $this->ordering = $campaignTypeOrder;
        $this->campaignTypesName = config('constants.CAMPAIGN_TYPES');
    }

    /**
     * Set the ordeing data and limit data
     * Primary: get the mix coreg order, if not available
     * Fallback: get/use the campaig type ordering, if not available
     * Order by priority(set in query params)
     * Set limit that was provided in service provider
     */
    public function setOrderAndLimits(array $param)
    {
        [$limit, $revenueTrackerLimit, $revenueTrackerID, $campaignTypeOrder] = $param;

        /* GET ORDERING */
        $this->mixCoregOrder->get($revenueTrackerID);

        /* GET CAMPAIGN ORDER PER CAMPAIGN TYPE ORDER AS FALLBACK */
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
        // check if mix coreg ordering is available
        if ($this->mixCoregOrder->hasOrder()) {
            $this->orderType = 'Mixed coreg type';

            return true;
        }
        // check if campaign type ordering is available
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
        /* 1ST STEP - CHECK CAMPAIGN TYPE IS INCLUDED IN MIX COREG ORDERING*/
        // If the mixcoreg order is available.
        if ($this->mixCoregOrder->has($campaign->campaign_type)) {
            $this->stackByMixCoregOrder($campaign->id);

            return;
        }

        /* 2ST STEP - CHECK CAMPAIGN TYPE IS INCLUDED IN MIX CAMPAIGN TYPE ORDERING*/
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

        /* 3RD STEP - DEFAULT STACKING BY CAMPAIGN TYPE*/
        array_push($this->stack[$campaign->campaign_type][$lastSet], $campaign->id);
    }

    /**
     * Implement mix coreg order
     */
    protected function stackByMixCoregOrder(int $campaignID)
    {
        if ($this->mixCoregOrder->campaignIdExists($campaignID)) {
            // Pre populate
            if (! array_key_exists($this->mixCoregOrder->tempIndex(), $this->stack)) {
                $this->stack[$this->mixCoregOrder->tempIndex()][0] = [];
            }
            //get last array
            $lastSet = count($this->stack[$this->mixCoregOrder->tempIndex()]) - 1;
            // Populate
            $this->stack = $this->mixCoregOrder->stack($campaignID, $lastSet, $this->stack);
        }
    }

    /**
     * Process first level limit and distributing is to coreg types
     */
    protected function implementCoregsLimit()
    {
        $this->stack = $this->limit->mixedCoregLimit->apply([
            $this->stack,
            $this->limit->limitPerPage(),
            $this->mixCoregOrder->tempIndex(),
            $this->mixCoregOrder->hasOrder(),
            $this->limit->getPathLimit(),
            $this->revenueTrackerLimit,
        ]);
    }
}
