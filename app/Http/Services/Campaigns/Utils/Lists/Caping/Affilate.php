<?php

namespace App\Http\Services\Campaigns\Utils\Lists\Caping;

use App\AffiliateCampaign;
use DB;

final class Affilate
{
    /**
     * Check affiliate cap
     *
     * @param  collection  $campaign
     * @return bolean
     */
    public function passed($campaign, $revenueTrackerID)
    {
        $affCapPassed = true;
        $campaignStatus = $campaign->status;

        $affiliateCampaignCap = $this->set($campaign->campaign_type, $campaign->firstAffiliateCampaign);
        //$affiliateCampaignCap = $this->get($campaign->id, $campaign->campaign_type, $revenueTrackerID);

        //If campaign is private or ...
        if ($campaignStatus == 1 || $affiliateCampaignCap) {
            if ($affiliateCampaignCap) { //check if affiliate campaign exists and default campaign cap is passed
                if ($affiliateCampaignCap->lead_cap_type != 0) { // if cap != unlimited, then check
                    //get affiliate lead count for campaign
                    $totalAffCmpLeadCount = $affiliateCampaignCap->lead_count == null ? 0 : $affiliateCampaignCap->lead_count;

                    if ($totalAffCmpLeadCount > 0) {
                        if ($affiliateCampaignCap->lead_cap_value <= $totalAffCmpLeadCount) {
                            $affCapPassed = false;
                        }
                    } else {
                        // lead count doesn't exists therefore, campaign has no count. PASSED
                        $affCapPassed = true;
                    }
                }
            } else {
                $affCapPassed = false;
            }
        }

        return $affCapPassed;
    }

    /**
     * Set the affiliate cap per campaign
     *
     * @param  int  $campaignType
     * @param  collection  $affiliateCap
     * @return object
     */
    protected function set($campaignType, $affiliateCap)
    {
        if (! $affiliateCap) {
            return '';
        }

        $object = new \stdClass;
        $object->lead_cap_type = $affiliateCap->lead_cap_type;
        $object->lead_cap_value = $affiliateCap->lead_cap_value;
        $object->lead_count = 0;

        if ($campaignType == 5) {
            if ($linkout = $affiliateCap->linkOutCount) {
                $object->lead_count = ($linkout->count == null) ? 0 : $linkout->count;
            }

            return $object;
        }

        if ($lead = $affiliateCap->leadCount) {
            $object->lead_count = ($lead->count == null) ? 0 : $lead->count;
        }

        return $object;
    }

    /**
     * Get the affiliate cap per campaign
     *
     * @param  int  $ampaignID
     * @param  int  $campaign_type
     * @param  int  $revenueTrackerID
     * @return collection
     */
    protected function get($campaignID, $campaignType, $revenueTrackerID)
    {
        // Fetch lead_cap_type, lead_cap_value of affiliate campaign
        return AffiliateCampaign::where(['campaign_id' => $campaignID, 'affiliate_id' => $revenueTrackerID])
            ->select('lead_cap_type', 'lead_cap_value', DB::raw($this->dBRawSelect($campaignType)))
            ->first();
    }

    /**
     * Set the query
     *
     * @param  int  $campaignType
     */
    protected function dBRawSelect($campaignType)
    {
        // Rew sql for campaign type
        if ($campaignType == 5) {
            return '(
            SELECT count FROM link_out_counts
            WHERE campaign_id = affiliate_campaign.campaign_id
            AND affiliate_id = affiliate_campaign.affiliate_id
            ) AS lead_count';
        }

        return '(
         SELECT count FROM lead_counts
         WHERE campaign_id = affiliate_campaign.campaign_id
         AND affiliate_id = affiliate_campaign.affiliate_id
      ) AS lead_count';
    }
}
