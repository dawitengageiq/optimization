<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AffiliateCampaign extends Model
{
    protected $connection;

    protected $table = 'affiliate_campaign';

    protected $fillable = [
        'campaign_id',
        'affiliate_id',
        'lead_cap_type',
        'lead_cap_value',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') == 'reports') {
            $this->connection = 'secondary';
        }
    }

    public function campaign()
    {
        return $this->belongsTo(\App\Campaign::class);
    }

    public function affiliate()
    {
        return $this->belongsTo(\App\Affiliate::class);
    }

    public function linkOutCount()
    {
        return $this->hasOne(LinkOutCount::class, 'campaign_id', 'campaign_id');
    }

    public function leadCount()
    {
        return $this->hasOne(LeadCount::class, 'campaign_id', 'campaign_id');
    }

    public function scopeGetCapDetails($query, $params)
    {
        if (isset($params['campaign_id'])) {
            $query->where('campaign_id', '=', $params['campaign_id']);
        }

        if (isset($params['affiliate_id'])) {
            $query->where('affiliate_id', '=', $params['affiliate_id']);
        }

        return $query;
    }

    public function scopeCampaignAffiliateManagementTable($query, $search, $start, $length, $order_col, $order_dir, $affiliates)
    {
        $query->join('campaigns', 'campaigns.id', '=', 'affiliate_campaign.campaign_id');

        $query->whereIn('affiliate_campaign.affiliate_id', $affiliates);

        if (! empty($search)) {
            $campaignStatuses = array_map('strtolower', config('constants.CAMPAIGN_STATUS'));
            $campaignTypes = array_map('strtolower', config('constants.CAMPAIGN_TYPES'));

            if (is_numeric($search)) {
                if (floor($search) == $search) {
                    $query->orWhere('campaigns.id', $search);
                }
            }

            $query->orWhere('campaigns.name', 'LIKE', '%'.$search.'%');

            $search_is_campaign_status = array_search($search, $campaignStatuses);
            if ($search_is_campaign_status !== false) {
                $query->orWhere('campaigns.status', $search_is_campaign_status);
            }

            $search_maybe_campaign_type = [];
            foreach ($campaignTypes as $id => $type) {
                if (stripos($type, strtolower($search)) !== false) {
                    $search_maybe_campaign_type[] = $id;
                }
            }
            if (count($search_maybe_campaign_type) > 0) {
                $query->orWhere(function ($query) use ($search_maybe_campaign_type) {
                    $query->whereIn('campaigns.campaign_type', $search_maybe_campaign_type);
                });
            }
        }

        $query->where('campaigns.id', '!=', env('EIQ_IFRAME_ID', 0));

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

    public function scopeCampaignAffiliateSearch($query, $campaign, $countOnly, $search, $start, $length, $order_col, $order_dir)
    {
        if (! $countOnly) {
            $query->join('affiliates', 'affiliates.id', '=', 'affiliate_campaign.affiliate_id');
        }

        if (! empty($search)) {
            $leadCapTypes = array_map('strtolower', config('constants.LEAD_CAP_TYPES'));

            if (is_numeric($search)) {
                if (floor($search) == $search) {
                    // $query->orWhere('affiliates.id',$search);
                    $query->orWhere('affiliates.id', 'LIKE', '%'.$search.'%');
                }
            }

            $query->orWhere('affiliates.company', 'LIKE', '%'.$search.'%');

            $search_is_cap_type = array_search($search, $leadCapTypes);
            if ($search_is_cap_type !== false) {
                $query->orWhere('affiliate_campaign.lead_cap_type', $search_is_cap_type);
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
}
