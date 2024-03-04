<?php

namespace App\Http\Services\Campaigns\Utils\Lists\Ordering;

final class ByMixCoreg
{
    /**
     * Default variables
     */
    protected $model;

    protected $campaignOrdering = '';

    protected $hasOrder = false;

    // Temporary index as campaign type id
    // As of this moment, there is no 1000 as campaign type id
    protected $tempIndx = 1000;

    public function __construct(\App\MixedCoregCampaignOrder $mixedCoregnOrder)
    {
        $this->model = $mixedCoregnOrder;

        $types = config('constants.CAMPAIGN_TYPES');
        end($types);
        $this->tempIndx = key($types) + 1;
    }

    /**
     * Get the affiliate cap per campaign
     *
     * @param  int|bolean  $defaultOrder
     * @param  int  $revenueTrackerID
     * @param  int  $campaignTypID
     *
     * @var  array
     */
    public function get($revenueTrackerID)
    {
        if ($campaignOrdering = $this->model->select('campaign_id_order')
            ->where('revenue_tracker_id', $revenueTrackerID)
            ->where('campaign_id_order', '!=', '[]')
            ->where('campaign_id_order', '!=', '')
            ->value('campaign_id_order')
        ) {
            $this->campaignOrdering = json_decode($campaignOrdering, true);
            $this->hasOrder = true;
        }
    }

    /**
     * Check ordering exists
     *
     * @return bolean
     */
    public function hasOrder()
    {
        return ($this->hasOrder) ? true : false;
    }

    /**
     * get the temporary index
     *
     * @return int
     */
    public function tempIndex()
    {
        return $this->tempIndx;
    }

    /**
     * Check campaign type exist on campaign type ordering
     *
     * @param  int  $campaignType
     *
     * @var array
     *
     * @return bolean
     */
    public function has($campaignType)
    {
        $coregTypes = array_keys(config('constants.MIXED_COREG_TYPE_FOR_ORDERING'));

        if ($this->hasOrder() && in_array($campaignType, $coregTypes)) {
            return true;
        }

        return false;
    }

    /**
     * Check if campaign id exists in campaign type ordering
     *
     * @param  int  $campaignID
     * @return bolean
     */
    public function campaignIdExists($campaignID)
    {
        if (in_array($campaignID, $this->campaignOrdering)) {
            return true;
        }

        return false;
    }

    /**
     * Stack campaigns order by campaign type ordering
     *
     * @param  int  $campaignID
     * @param  int  $lastSet
     * @param  array  $stack
     * @return array
     */
    public function stack($campaignID, $lastSet, $stack)
    {
        $stack[$this->tempIndex()][$lastSet][array_search($campaignID, $this->campaignOrdering)] = $campaignID;

        return $stack;
    }
}
