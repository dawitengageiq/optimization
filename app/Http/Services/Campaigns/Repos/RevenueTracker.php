<?php

namespace App\Http\Services\Campaigns\Repos;

class RevenueTracker
{
    /**
     * Default variables
     */
    protected $revenueTracker;

    protected $crgLimit;

    protected $extLimit;

    protected $lnkLimit;

    protected $exitLimit;

    protected $pathType = 2;

    protected $orderStatus = 0;

    protected $revenueTrackerID = 1;

    protected $mixedCoregLimit;

    protected $orderType;

    protected $subidBreakdown = 0;

    protected $s1Breakdown = 0;

    protected $s2Breakdown = 0;

    protected $s3Breakdown = 0;

    protected $s4Breakdown = 0;

    protected $s5Breakdown = 0;

    protected $params = [
        'affiliate_id' => '',
        'campaign_id' => '',
        'offer_id' => '',
        's1' => '',
        's2' => '',
        's3' => '',
        's4' => '',
        's5' => '',
    ];

    protected $model;

    /**
     * Intantiate, dependency injection of model
     */
    // public function __construct (\Illuminate\Database\Eloquent\Model $model)
    public function __construct(\App\AffiliateRevenueTracker $model)
    {
        $this->model = $model;
    }

    /**
     * Get the affiliate revenue tracker details
     */
    public function get($userDetails)
    {
        // Precaution: check if user details is available
        // If not: use the default variables
        if (! $userDetails) {
            $this->defaultRevenueTracker();
        }

        // Populate parameters
        $this->params = array_replace($this->params, $userDetails);

        if ($revenueTracker = $this->model->select(
            'revenue_tracker_id',
            'path_type',
            'order_status',
            'crg_limit',
            'ext_limit',
            'lnk_limit',
            'order_type',
            'mixed_coreg_campaign_limit',
            'subid_breakdown',
            'sib_s1',
            'sib_s2',
            'sib_s3',
            'sib_s4'
        )
            ->where('affiliate_id', $this->params['affiliate_id'])
            ->where('campaign_id', $this->params['campaign_id'])
            ->where('offer_id', $this->params['offer_id'])
            // ->where('s1', $this->params['s1'])
            // ->where('s2', $this->params['s2'])
            // ->where('s3', $this->params['s3'])
            // ->where('s4', $this->params['s4'])
            // ->where('s5', $this->params['s5'])
            ->first()
        ) {
            $this->setDetails($revenueTracker);

            return;
        }

        $this->defaultRevenueTracker();
    }

    /**
     * Get the affiliate default revenue tracker details
     */
    public function defaultRevenueTracker()
    {
        if ($revenueTracker = $this->model->select(
            'path_type',
            'order_status',
            'crg_limit',
            'ext_limit',
            'lnk_limit',
            'order_type',
            'mixed_coreg_campaign_limit',
            'subid_breakdown',
            'sib_s1',
            'sib_s2',
            'sib_s3',
            'sib_s4'
        )
            ->where('revenue_tracker_id', 1)
            ->first()
        ) {
            $this->setDetails($revenueTracker);
        }
    }

    /**
     * Set the external revenue tracker
     */
    public function external($userDetails)
    {
        // Get data for revenue tracker in database
        if ($userDetails['affiliate_id'] && @$userDetails['campaign_id'] && @$userDetails['offer_id']) {
            $this->get($userDetails);

            return;
        }

        // Revenue tacker id = affiliate id
        $this->revenueTrackerID = $userDetails['affiliate_id'];

        $this->setDetails([
            'path_type' => $this->pathType,
            'order_status' => $this->orderStatus,
            'crg_limit' => $this->crgLimit,
            'ext_limit' => $this->extLimit,
            'lnk_limit' => $this->lnkLimit,
            'order_type' => $this->orderType,
            'mixed_coreg_campaign_limit' => $this->mixedCoregLimit,
            'subid_breakdown' => $this->subidBreakdown,
        ]);
    }

    /**
     * [setDetails description]
     *
     * @param [type] $details [description]
     */
    public function setDetails($details)
    {
        if (! $details) {
            return;
        }

        $this->revenueTracker = $details;

        if ($details instanceof \Illuminate\Database\Eloquent\Model) {
            $details = $details->toArray();
        }

        $this->crgLimit = $details['crg_limit'];
        $this->extLimit = $details['ext_limit'];
        $this->lnkLimit = $details['lnk_limit'];
        $this->pathType = $details['path_type'];
        $this->orderType = $details['order_type'];
        $this->orderStatus = $details['order_status'];
        $this->mixedCoregLimit = $details['mixed_coreg_campaign_limit'];
        $this->subidBreakdown = $details['subid_breakdown'];
        $this->s1Breakdown = $details['sib_s1'];
        $this->s2Breakdown = $details['sib_s2'];
        $this->s3Breakdown = $details['sib_s3'];
        $this->s4Breakdown = $details['sib_s4'];
        if (array_key_exists('revenue_tracker_id', $details)) {
            $this->revenueTrackerID = $details['revenue_tracker_id'];
        }
    }

    /**
     * Get the revenue tracker details
     */
    public function details()
    {
        return (is_object($this->revenueTracker)) ? $this->revenueTracker->toArray() : $this->revenueTracker;
    }

    /**
     * Get the revenue tracker id
     */
    public function trackerID()
    {
        return $this->revenueTrackerID;
    }

    /**
     * Get the coreg limiit
     */
    public function coregLimit()
    {
        return $this->crgLimit;
    }

    /**
     * Get the external limiit
     */
    public function externalLimit()
    {
        return $this->extLimit;
    }

    /**
     * Get the linkout limiit
     */
    public function linkOutLimit()
    {
        return $this->lnkLimit;
    }

    /**
     * Get the exit limiit
     */
    public function exitLimit()
    {
        return $this->exitLimit;
    }

    /**
     * Get the path type
     */
    public function pathType()
    {
        return $this->pathType;
    }

    /**
     * Get the order status
     */
    public function orderStatus()
    {
        return $this->orderStatus;
    }

    /**
     * Get the default openssl_private_decrypt
     */
    public function defaultOrder()
    {
        return $this->defaultOrder;
    }

    /**
     * Get the order type
     */
    public function orderType()
    {
        $orderType = config('constants.PATH_ORDER_TYPE');

        if (array_key_exists($this->orderType, $orderType)) {
            return $orderType[$this->orderType];
        }

        return $this->orderType;
    }

    /**
     * Get the order type
     */
    public function mixedCoregLimit()
    {
        return $this->mixedCoregLimit;
    }

    public function subidBreakdown()
    {
        return $this->subidBreakdown;
    }

    public function s1Breakdown()
    {
        return $this->s1Breakdown;
    }

    public function s2Breakdown()
    {
        return $this->s2Breakdown;
    }

    public function s3Breakdown()
    {
        return $this->s3Breakdown;
    }

    public function s4Breakdown()
    {
        return $this->s4Breakdown;
    }

    public function s5Breakdown()
    {
        return $this->s5Breakdown;
    }

    /**
     * Get all limiits
     */
    public function limit()
    {
        return [
            'coreg' => $this->crgLimit,
            'external' => $this->extLimit,
            'link_out' => $this->lnkLimit,
            'exit' => $this->exitLimit,
        ];
    }
}
