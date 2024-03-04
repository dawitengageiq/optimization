<?php

namespace App\Http\Services\Campaigns\Repos;

use DB;

final class CampaignList
{
    /**
     * Default variables
     */
    protected $model;

    protected $query;

    /**
     * Intantiate, dependency injection of model
     */
    public function __construct(\App\Campaign $model)
    {
        $this->model = $model;
    }

    /**
     * Set the params and the method to access
     *
     * @param  array  $params
     * @return object class
     */
    public function setParams($params)
    {
        if (! is_array($params)) {
            return $this;
        }

        collect($params)->each(function ($param, $idx) {
            if (method_exists($this, camelCase($idx))) {
                $this->{camelCase($idx)}($param);
            }
        });

        return $this;
    }

    /**
     * Get
     *
     * @return collection
     */
    public function get()
    {
        if ($this->query != null) {
            return $this->query->get();
        }

        return '';
    }

    /**
     * Select
     *
     * @param  int|array  $select
     *
     * @var sql
     *
     * @return object class
     */
    protected function select($select = '')
    {
        if (! $select || count($select) > 0) {
            $this->query = $this->model->select(
                'id', 'name', 'advertiser_id', 'status', 'lead_cap_type', 'lead_cap_value',
                'default_received', 'default_payout', 'priority', 'campaign_type', 'linkout_offer_id',
                //Get lead counts when campaign type is 5
                DB::raw('
                        (CASE WHEN campaign_type = 5 THEN
                            (SELECT count FROM link_out_counts WHERE campaign_id = campaigns.id AND affiliate_id IS NULL LIMIT 1) ELSE
                            (SELECT count FROM lead_counts WHERE campaign_id = campaigns.id AND affiliate_id IS NULL LIMIT 1) END
                        ) AS lead_count'
                )
            );

            return $this;
        }

        $this->query = $this->model->select($select);

        return $this;
    }

    /**
     * Status
     *
     * @param  int|array  $status
     *
     * @var sql
     *
     * @return object class
     */
    protected function status($status = '')
    {
        if (! $status || count($status) > 0) {
            $this->query->where('status', '!=', 0);
            $this->query->where('status', '!=', 3);

            return $this;
        }

        foreach ($status as $code => $expression) {
            $this->query->where('status', $expression, $code);
        }

        return $this;
    }

    /**
     * Included in the list of campaign type
     *
     * @param  int|array  $campaignTypeOrder
     *
     * @var sql
     *
     * @return object class
     */
    protected function inCampaignType($campaignTypeOrder = '')
    {
        if (! $campaignTypeOrder) {
            return $this;
        }

        if (is_array($campaignTypeOrder)) {
            $this->query->whereIn('campaign_type', $campaignTypeOrder);
        }

        return $this;
    }

    /**
     * Exclude campaign ids
     *
     * @param int|array excludedCampaignIds
     *
     * @var sql
     *
     * @return object class
     */
    protected function excludeCampaignIDs($excludedCampaignIds = '')
    {
        if (! $excludedCampaignIds) {
            return $this;
        }

        if (is_array($excludedCampaignIds)) {
            $this->query->whereNotIn('id', $excludedCampaignIds);
        }

        return $this;
    }

    /**
     * With filter groups
     *
     * @var sql
     *
     * @return object class
     */
    protected function withFilterGroups($param = '')
    {
        if ($param) {
            // Do something
        }

        $this->query->with(['filter_groups' => function ($q) {
            $q->select('id', 'campaign_id', 'status')
                // Status should be active
                ->where('status', 1)
                // Retrieve filters if filter group status is active
                ->with('filters');
        }]);

        return $this;
    }

    /**
     * With affiliate campaign
     *
     * @param  int  $revenueTrackerID
     *
     * @var sql
     *
     * @return object class
     */
    protected function withAffiliateCampaign($revenueTrackerID = '')
    {
        if (! $revenueTrackerID) {
            return $this;
        }

        $this->query->with(['firstAffiliateCampaign' => function ($q) use ($revenueTrackerID) {
            $q->select('campaign_id', 'lead_cap_type', 'lead_cap_value')
                ->where('affiliate_id', $revenueTrackerID)
                ->with(['linkOutCount' => function ($q) use ($revenueTrackerID) {
                    $q->select('campaign_id', 'count')
                        ->where('affiliate_id', $revenueTrackerID);
                }])
                ->with(['leadCount' => function ($q) use ($revenueTrackerID) {
                    $q->select('campaign_id', 'count')
                        ->where('affiliate_id', $revenueTrackerID);
                }]);
            //->first();
        }]);

        return $this;
    }

    /**
     * With no tracker
     *
     * @param  string  $email
     *
     * @var sql
     *
     * @return object class
     */
    protected function withNoTracker($email = '')
    {
        if (! $email) {
            return $this;
        }

        $this->query->with(['noTracker' => function ($q) use ($email) {
            // Retrieve only needed fields
            $q->select('campaign_id', 'count')
            // Only iwth email record
                ->where('email', $email);
        }]);

        return $this;
    }

    /**
     * With creatives
     *
     * @var sql
     *
     * @return object class
     */
    protected function withCreatives($param = '')
    {
        if ($param) {
            // Do something
        }

        $this->query->with(['creatives' => function ($q) {
            // Retrieve only needed fields
            $q->select('id', 'campaign_id', 'weight')
                // The weight is not zero
                ->where('weight', '!=', 0);
        }]);

        return $this;
    }

    /**
     * With campaign config
     *
     * @var sql
     *
     * @return object class
     */
    protected function withConfig($param = '')
    {
        if ($param) {
            // Do something
        }

        $this->query->with(['config' => function ($q) {
            $q->select('id', 'ping_url', 'ping_success');
        }]);

        return $this;
    }

    /**
     * Order by
     *
     * @param  string|array  $orderBy
     *
     * @var sql
     *
     * @return object class
     */
    protected function orderBy($orderBy = '')
    {
        if (! $orderBy || count($orderBy) > 0) {
            $this->query->orderBy('priority', 'ASC');

            return $this;
        }

        $this->query->orderBy($orderBy[0], $orderBy[1]);

        return $this;
    }
}
