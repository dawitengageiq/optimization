<?php

namespace App\Http\Services\Campaigns\Utils\Lists\Stacking;

use Config;

class ByPriorityWithJson
{
    /*
     * Default variables
     *
     */
    public $lists = [];

    /**
     * Initialize, Inject dependencies
     * We didn't inject ordering for this is default stacking that used order by priority in query string.
     */
    public function __construct()
    {
    }

    /**
     * Stack the qualified campaign
     * Called from App\Http\Services\Campaigns\Factories\ListsFactory
     */
    public function insertIntoStack(collection $campaign)
    {
        // Mixed Coregs
        if (in_array($campaign->campaign_type, array_keys(Config::get('constants.MIXED_COREG_TYPE_FOR_ORDERING')))) {
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
     * Stack the qualified campaign coregs
     */
    protected function stackCampaignCoreg(collection $campaign)
    {
        // Pre populate
        if (! array_key_exists($campaign->campaign_type, $this->lists)) {
            $this->lists[$campaign->campaign_type][0] = [];
        }

        //get last array
        $lastSet = count($this->lists[$campaign->campaign_type]) - 1;

        // Push campaign id to lists.
        array_push($this->lists[$campaign->campaign_type][$lastSet], $campaign->id);
    }

    /**
     * Stack the qualified campaign other coregs and exit page
     *
     *
     * @var array lists
     */
    protected function stackOtherCampaigns(collection $campaign)
    {
        // Pre populate
        if (! array_key_exists($campaign->campaign_type, $this->lists)) {
            $this->lists[$campaign->campaign_type][0] = [];
        }

        //get last array
        $lastSet = count($this->lists[$campaign->campaign_type]) - 1;

        // Push campaign id to lists.
        array_push($this->lists[$campaign->campaign_type][$lastSet], $campaign->id);
    }

    /**
     * Stack the qualified campaign externals and long forms
     */
    protected function stackExternalAndLongFormCampaign(collection $campaign)
    {
        $key = 0;
        //1 Display per Page: External, Long Form (1st Grp) & (2nd Grp)
        if (isset($this->lists[$campaign->campaign_type])) {
            $key = count($this->lists[$campaign->campaign_type]);
        }

        $this->lists[$campaign->campaign_type][$key][] = $campaign->id;
    }
}
