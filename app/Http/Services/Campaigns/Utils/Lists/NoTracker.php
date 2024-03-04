<?php

namespace App\Http\Services\Campaigns\Utils\Lists;

final class NoTracker
{
    /*
     * Default variables
     *
     */
    protected $countLimit = 5;

    /**
     * Check affiliate cap
     *
     * @param  collection  $affiliateCampaignCap
     */
    public function passed($campaign, $limit)
    {
        // False when campaign is not legit.
        if (! $campaign || ! is_object($campaign)) {
            return false;
        }

        // Override default limit;
        if (is_int($limit) && $limit > 0) {
            $this->countLimit = $limit;
        }

        // Compare if campaign have records
        if ($campaign->noTracker) {
            if ($campaign->noTracker->count >= $this->countLimit) {
                return false;
            }
        }

        return true;
    }
}
