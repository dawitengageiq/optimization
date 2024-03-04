<?php

namespace App\Http\Services\Campaigns\Utils\Lists\Caping;

final class Campaign
{
    /**
     * Check default campaign cap
     *
     * @param  collection  $campaign
     * @return bolean
     */
    public function passed($campaign)
    {
        //if default cap is not unlimited or 0, check campaign lead counts
        if ($campaign->lead_cap_type != 0) {
            $totalLeadCount = $campaign->lead_count == null ? 0 : $campaign->lead_count;
            if ($totalLeadCount) {
                //if default lead cap value less than or equal to lead count, FAILED
                if ($campaign->lead_cap_value <= $totalLeadCount) {
                    return false;
                }
            }
        }

        return true;
    }
}
