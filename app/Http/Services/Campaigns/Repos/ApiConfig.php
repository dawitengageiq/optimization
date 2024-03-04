<?php

namespace App\Http\Services\Campaigns\Repos;

final class ApiConfig
{
    /**
     * Default variables
     */
    protected $apiConfigs;

    protected $campaignTypeOrder = [1, 2, 8, 13];

    protected $excludedCampaignID = [];

    protected $displayLimit = 20;

    protected $multiPage = 0;

    protected $timeInterval = 24;

    protected $model;

    /**
     * Intantiate, dependency injection of model
     */
    public function __construct(\App\AffiliateApiConfigs $model)
    {
        $this->model = $model;
    }

    /**
     * Get the affiliate api configs
     *
     * @param  int  $affiliateID
     */
    public function get($affiliateID)
    {
        // Precaution: check if table is available or affiliate id is available
        // If not: use the default variables
        if (! $affiliateID) {
            return;
        }

        // Query the configs and overwrite defaults
        if ($affApiConfigs = $this->model->where('affiliate_id', $affiliateID)->first()) {
            $this->setDetails($affApiConfigs);
        }
    }

    /**
     * Get the affiliate api configs details
     */
    public function details()
    {
        return $this->apiConfigs;
    }

    /**
     * Set the affiliate api configs details
     */
    public function setDetails($details)
    {
        if (! $details) {
            return;
        }

        $this->apiConfigs = $details;

        if ($details instanceof \Illuminate\Database\Eloquent\Model) {
            $details = $details->toArray();
        }

        $this->campaignTypeOrder = json_decode($details['campaign_type_order']);
        $this->excludedCampaignID = json_decode($details['excluded_campaign_id']);
        $this->displayLimit = $details['display_limit'];
        $this->multiPage = $details['multi_page'];
        $this->timeInterval = $details['time_interval'];
    }

    /**
     * Get the affiliate campaign type order
     */
    public function campaignTypeOrder()
    {
        return $this->campaignTypeOrder;
    }

    /**
     * Get the the list of excluded campaign ids
     */
    public function excludedCampaignID()
    {
        return $this->excludedCampaignID;
    }

    /**
     * Get the affiliate display limit
     */
    public function displayLimit()
    {
        return $this->displayLimit;
    }

    /**
     * Get the affiliate time interval in hours
     */
    public function timeInterval()
    {
        return $this->timeInterval;
    }

    /**
     * Get the affiliate display limit
     */
    public function isMultiPage()
    {
        return ($this->multiPage) ? true : false;
    }
}
