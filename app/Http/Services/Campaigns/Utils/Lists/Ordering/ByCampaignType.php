<?php

namespace App\Http\Services\Campaigns\Utils\Lists\Ordering;

final class ByCampaignType
{
    /**
     * Default variables
     */
    protected $model;

    protected $campaignOrdering = [];

    protected $campaignTypeOrder = [];

    protected $typeOrderCount = [];

    public function __construct(\App\CampaignTypeOrder $campaignTypeOrder)
    {
        $this->model = $campaignTypeOrder;
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
    public function get($revenueTrackerID, $campaignTypID)
    {
        $campaignTypeOrder = $this->model->select('campaign_type_id', 'campaign_id_order')
            ->whereIn('campaign_type_id', $campaignTypID)
            ->where('revenue_tracker_id', $revenueTrackerID)
            ->where('campaign_id_order', '!=', '[]')
            ->get()
            ->keyBy('campaign_type_id');

        $this->campaignTypeOrder = $campaignTypeOrder->map(
            function ($order, $typeID) {
                $this->typeOrderCount[$typeID] = count(json_decode($order['campaign_id_order']));

                return json_decode($order['campaign_id_order']);
            }
        )->toArray();
    }

    /**
     * Check ordering exists
     *
     * @return bolean
     */
    public function hasOrder()
    {
        return (count($this->campaignOrdering)) ? true : false;
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
        $this->campaignOrdering = [];

        if (array_key_exists($campaignType, $this->campaignTypeOrder)) {
            $this->campaignOrdering = $this->campaignTypeOrder[$campaignType];

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
     * @param  int  $campaignType
     * @param  int  $lastSet
     * @param  array  $stack
     * @return array
     */
    public function stack($campaignID, $campaignType, $lastSet, $stack)
    {
        $stack[$campaignType][$lastSet][array_search($campaignID, $this->campaignOrdering)] = $campaignID;
        // sort indexes
        ksort($stack[$campaignType][$lastSet]);

        return $stack;
    }

    /**
     * Stack campaigns order by campaign type ordering
     *
     * @param  int  $campaignID
     * @param  int  $campaignType
     * @param  int  $lastSet
     * @param  array  $stack
     * @return array
     */
    public function push($campaignID, $campaignType, $lastSet, $stack)
    {
        $stack[$campaignType][$lastSet][$this->typeOrderCount[$campaignType]] = $campaignID;
        // sort indexes
        ksort($stack[$campaignType][$lastSet]);
        // Increment to be the next indexes;
        $this->typeOrderCount[$campaignType]++;

        return $stack;
    }
}
