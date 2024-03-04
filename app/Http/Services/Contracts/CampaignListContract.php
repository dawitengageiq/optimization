<?php

namespace App\Http\Services\Contracts;

interface CampaignListContract
{
    /**
     * Set user details
     *
     * @param  array  $userDetails
     *
     * @var array $userDetails
     */
    public function setUserDetails($userDetails);

    /**
     * Set the campaign type order, will be used in campaign query
     *
     * @param  array  $campaign_type
     *
     * @var array $campaign_type
     */
    public function setTypeOrdering(array $typeOrdering);

    /**
     * Query campaigns with relationship
     */
    public function getCampaigns($affiliateID);

    /**
     * Get the  Qualified Campaigns
     *
     * @param  bolean  $filter
     * @param  int  $path_type
     * @param  int  $revenue_tracker_id
     * @return array
     */
    public function filterCampaigns(
        $filter,
        $pathType,
        $revenueRrackerID);
}
