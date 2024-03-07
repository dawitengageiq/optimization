<?php

namespace App\Http\Services\Campaigns\Utils\Lists\Stacking;

use Illuminate\Support\Facades\Config;

class ByPriority implements \App\Http\Services\Campaigns\Utils\Lists\Contracts\StackContract
{
    /*
     * Default variables
     *
     */
    protected $exit;

    protected $campaignTypesName;

    protected $orderType = '';

    protected $stack = [];

    protected $typeOrder = [];

    protected $campaignTypesOrder = [];

    /**
     * Initialize, Inject dependencies
     * We didn't inject ordering for this is default stacking that used order by priority in query string.
     */
    public function __construct(
        \App\Http\Services\Campaigns\Utils\Lists\Contracts\LimitContract $limit,
        \App\Http\Services\Campaigns\Utils\Lists\ExitPage $exitPage
    ) {
        $this->limit = $limit;
        $this->exitPage = $exitPage;
        $this->campaignTypesName = config('constants.CAMPAIGN_TYPES');
    }

    /**
     * Set the typeorder that will be used in function get below
     * This array was used in query string to make sure to get only campaigns with campaign types that are in this list, and
     * When ordering in get function, we are sure that all campaigns passes the filter will also pass this ordering
     * This type of ordering is the arrangement/ordering of campaign type.
     *
     * @param  array  $typeOrdering
     *
     * @var array
     */
    public function setTypeOrdering($typeOrder)
    {
        $this->typeOrder = $typeOrder;
    }

    /**
     * Set the ordeing data and limit data
     * The ordering is is second level ordering, means ordering per campaign type or ordering per pages
     * Limits was set in service provider
     * Since this is default stacking, means we use ordering of query string, and
     * We dont nedd to use other type ordering.
     */
    public function setOrderAndLimits(array $param)
    {
        [$limit, $revenueTrackerLimit] = $param;

        /* Set limit per campaign type */
        $this->limit->set($limit);
        $this->revenueTrackerLimit = $revenueTrackerLimit;
    }

    /**
     * Check has ordering
     * return false for we use default query ordering and no custom ordering was used here.
     */
    public function hasOrder(): bool
    {
        return false;
    }

    /**
     * Get what type of ordering was used
     */
    public function orderType(): bool
    {
        return 'default';
    }

    /**
     * Stack the qualified campaign
     */
    public function stackCampaign(
        $campaign,
        int $pathType)
    {
        //Long Path
        if ($pathType == 1) {
            $this->passedCampaigns[] = $campaign->id;

            return;
        }

        // Mixed Coregs
        if (in_array($campaign->campaign_type, array_keys(config('constants.MIXED_COREG_TYPE_FOR_ORDERING')))) {
            $this->stackCampaignCoreg($campaign);

            return;
        }

        // External and long forms
        if ($campaign->campaign_type == 4
            || $campaign->campaign_type == 3
            || $campaign->campaign_type == 7
        ) {
            $this->stackExternalAndLongFormCampaign($campaign);

            return;
        }

        // Remaining campaigns
        $this->stackOtherCampaigns($campaign);

    }

    /**
     * Arrange the qualified campaigns by campaign type order
     */
    public function get(int $pathType)
    {
        if ($pathType == 1) {
            return $this->passedCampaigns;
        }

        // Since the coregs type was excluded in processFirstLevelLimit in ListsFactory
        // We will implemet it here
        $this->implementCoregsLimit();

        // $campaignTypesName;
        // $campaignTypesOrder;

        // Go through ordering of campaign type
        $qualifiedCampaigns = collect($this->typeOrder)
            // Go through the stack campaign type order collections
            ->map(function ($type) {
                // Check the campaign type(item) is't in the saved stack
                // if available: return the stack as new item of stack campaign type order collections
                if (array_key_exists($type, $this->stack)) {

                    // Campaign type name ordering
                    for ($i = 1; $i <= count($this->stack[$type]); $i++) {
                        $this->campaignTypesOrder[] = $type;
                        // $this->campaignTypesOrder[] = $this->campaignTypesName[$type];
                    }

                    // If exit page, do random
                    if ($type == array_search('Exit Page', Config::get('constants.CAMPAIGN_TYPES'))) {
                        // Return random in array
                        return $this->exitPage->randomID($this->stack, $type);
                    }

                    return $this->stack[$type];
                }
            })
            // Filter: remove empty indexes in stack campaign type order collections
            ->filter()
            // Since the Array is 3x deep, we need to flatten to one dimensional array
            ->flatMap(function ($stack) {
                // Sort the ordering by key
                ksort($stack[0]);
                // arrange the keys as incremental from 0
                $stack[0] = array_values($stack[0]);

                return $stack;
            })
            // Filter: remove empty indexes in stack campaign type order collections
            ->filter();

        // The return should be in collection format for there is function using this function that don't
        // need this as array but collection
        return $qualifiedCampaigns;
    }

    /**
     * Get the order of campaign type by name
     */
    public function getCampaignTypeNameOrder(): array
    {
        return $this->campaignTypesOrder;
    }

    /**
     * Stack the qualified campaign coregs
     *
     *
     * @var array
     */
    protected function stackCampaignCoreg($campaign)
    {
        // Pre populate
        if (! array_key_exists($campaign->campaign_type, $this->stack)) {
            $this->stack[$campaign->campaign_type][0] = [];
        }

        //get last array
        $lastSet = count($this->stack[$campaign->campaign_type]) - 1;

        // Push campaign id to stack.
        array_push($this->stack[$campaign->campaign_type][$lastSet], $campaign->id);
    }

    /**
     * Stack the qualified campaign other coregs and exit page
     *
     *
     * @var array
     */
    protected function stackOtherCampaigns($campaign)
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

        // Push campaign id to stack.
        array_push($this->stack[$campaign->campaign_type][$lastSet], $campaign->id);
    }

    /**
     * Stack the qualified campaign externals and long forms
     *
     *
     * @var int
     */
    protected function stackExternalAndLongFormCampaign($campaign)
    {
        // Implement her the limit of the campaign type
        if ($this->limit->exceed($campaign->campaign_type)) {
            return;
        }

        $key = 0;
        //1 Display per Page: External, Long Form (1st Grp) & (2nd Grp)
        if (isset($this->stack[$campaign->campaign_type])) {
            $key = count($this->stack[$campaign->campaign_type]);
        }

        $this->stack[$campaign->campaign_type][$key][] = $campaign->id;
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
