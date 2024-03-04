<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AffiliateRevenueTracker extends Model
{
    protected $connection;

    protected $table = 'affiliate_revenue_trackers';

    protected $fillable = [
        'affiliate_id',
        'campaign_id',
        'offer_id',
        'revenue_tracker_id',
        's1',
        's2',
        's3',
        's4',
        's5',
        'note',
        'tracking_link',
        'crg_limit',
        'ext_limit',
        'lnk_limit',
        'path_type',
        'website',
        'order_by',
        'order_status',
        'views',
        'mixed_coreg_order_by',
        'mixed_coreg_order_status',
        'mixed_coreg_campaign_views',
        'mixed_coreg_default_order',
        'landing_url',
        'exit_page_id',
        'subid_breakdown',
        'new_subid_breakdown_status',
        'report_subid_breakdown_status',
        'sib_s1',
        'sib_s2',
        'sib_s3',
        'sib_s4',
        'nsib_s1',
        'nsib_s2',
        'nsib_s3',
        'nsib_s4',
        'rsib_s1',
        'rsib_s2',
        'rsib_s3',
        'rsib_s4',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') == 'reports') {
            $this->connection = 'secondary';
        }
    }

    public function campaignTypeOrders()
    {
        return $this->hasMany(CampaignTypeOrder::class, 'revenue_tracker_id', 'revenue_tracker_id');
    }

    public function mixedCoregCampaignOrder()
    {
        return $this->hasone(MixedCoregCampaignOrder::class, 'revenue_tracker_id', 'revenue_tracker_id');
    }

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function campaignViewReports()
    {
        return $this->hasMany(CampaignViewReport::class, 'revenue_tracker_id', 'revenue_tracker_id');
    }

    public function affiliateCampaign()
    {
        return $this->hasMany(AffiliateCampaign::class, 'affiliate_id', 'revenue_tracker_id');
    }

    public function affiliateReport()
    {
        return $this->hasMany(AffiliateReport::class, 'revenue_tracker_id', 'revenue_tracker_id');
    }

    public function cpaInAffiliateReport()
    {
        return $this->hasOne(AffiliateReport::class, 'revenue_tracker_id', 'revenue_tracker_id');
    }

    public function clicksVsRegistrationStatistics()
    {
        return $this->hasOne(ClicksVsRegistrationStatistics::class, 'revenue_tracker_id', 'revenue_tracker_id');
    }

    public function pageViewStatistics()
    {
        return $this->hasOne(PageViewStatistics::class, 'revenue_tracker_id', 'revenue_tracker_id');
    }

    public function revenueTrackerCakeStatistic()
    {
        return $this->hasOne(RevenueTrackerCakeStatistic::class, 'revenue_tracker_id', 'revenue_tracker_id');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'affiliate_id', 'revenue_tracker_id');
    }

    public function cakeRevenue()
    {
        return $this->hasOne(CakeRevenue::class, 'revenue_tracker_id', 'revenue_tracker_id');
    }

    public function scopeCakePublisher($query, $revenueTrackerID)
    {
        return $query->where('revenue_tracker_id', '=', $revenueTrackerID)
            ->orderBy('id', 'ASC');
    }

    public function scopeWithAffiliates($query, $params)
    {
        $query->select('website', 'revenue_tracker_id', 'affiliate_id', 'affiliates.company AS affiliate_name', 'campaign_id')
            ->join('affiliates', 'affiliate_revenue_trackers.affiliate_id', '=', 'affiliates.id');

        if (isset($params['type'])) {
            $query->where('affiliates.type', '=', $params['type']);
        }

        return $query;
    }

    public function scopeSearchRevenueTrackers($query, $search, $start, $length, $order_col, $order_dir)
    {
        $query->join('affiliates', 'affiliate_revenue_trackers.affiliate_id', '=', 'affiliates.id');
        $query->join('affiliates as revTracker', 'affiliate_revenue_trackers.revenue_tracker_id', '=', 'revTracker.id');

        if (! empty($search)) {

            $query->where('website', 'LIKE', '%'.$search.'%');
            $query->orWhere('affiliates.company', 'LIKE', '%'.$search.'%');
            $query->orWhere('s1', 'LIKE', '%'.$search.'%');
            $query->orWhere('s2', 'LIKE', '%'.$search.'%');
            $query->orWhere('s3', 'LIKE', '%'.$search.'%');
            $query->orWhere('s4', 'LIKE', '%'.$search.'%');
            $query->orWhere('s5', 'LIKE', '%'.$search.'%');

            if (is_numeric($search) && floor($search) == $search) {
                $query->orWhere('revenue_tracker_id', $search);
                $query->orWhere('offer_id', $search);
                $query->orWhere('campaign_id', $search);
                $query->orWhere('affiliate_id', $search);
            }

            if (strtolower($search) == 'enabled') {
                $query->orWhere('subid_breakdown', 1);
            }

            if (strtolower($search) == 'disabled') {
                $query->orWhere('subid_breakdown', 0);
            }
        }

        if ($order_col != null && $order_dir != null) {
            if ($order_col > -1) {
                $query->orderBy($order_col, $order_dir);
            }
        }

        if ($start != null) {
            $query->skip($start);
        }

        if ($length != null) {
            $query->take($length);
        }

        return $query;
    }

    public function scopeGetRevenueTrackersWithExitPage($query, $params, $justForCount = true)
    {

        if (! $justForCount) {
            $query->join('affiliates', 'affiliate_revenue_trackers.affiliate_id', '=', 'affiliates.id');
        }

        if ($params['exit_page_id'] == '') {
            $query->whereNull('exit_page_id');
        } else {
            $query->where('exit_page_id', $params['exit_page_id']);
        }

        if ($params['search']['value'] != '') {
            $search = $params['search']['value'];

            if (is_numeric($search) && floor($search) == $search) {
                $query->where(function ($queryz) use ($search) {
                    $queryz->where('revenue_tracker_id', $search)
                        ->orWhere('affiliate_id', $search);
                });
            }
        }

        if (! $justForCount) {

            $order_col = isset($params['order_col']) ? $params['order_col'] : null;
            $order_dir = isset($params['order_dir']) ? $params['order_dir'] : null;
            if ($order_col != null && $order_dir != null) {
                if ($order_col > -1) {
                    $query->orderBy($order_col, $order_dir);
                }
            }

            if ($params['start'] != null) {
                $query->skip($params['start']);
            }

            if ($params['length'] != null) {
                $query->take($params['length']);
            }
        }

        return $query;
    }

    public function scopeGetAvailableRevTrackersForExitPage($query, $exit_page, $search, $disregard)
    {
        $query->select('id', 'revenue_tracker_id');
        $query->where('revenue_tracker_id', 'LIKE', '%'.$search.'%');

        if ($disregard != null) {
            $query->whereNotIn('id', $disregard);
        }

        if ($exit_page == null) {
            $query->whereNotNull('exit_page_id');
        } else {
            $query->where(function ($queryz) use ($exit_page) {
                $queryz->whereNull('exit_page_id')
                    ->orWhere('exit_page_id', '!=', $exit_page);
            });
        }
        $query->orderBy('revenue_tracker_id', 'asc');
    }
}
