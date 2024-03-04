<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class Affiliate extends Model
{
    protected $connection;

    protected $table = 'affiliates';

    protected $fillable = [
        'company',
        'website_url',
        'phone',
        'address',
        'city',
        'state',
        'zip',
        'description',
        'status',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') == 'reports') {
            $this->connection = 'secondary';
        }
    }

    // public function user(){
    //     return $this->belongsTo('App\User', 'id', 'id');
    // }

    public function user()
    {
        return $this->hasOne(\App\User::class, 'affiliate_id', 'id');
    }

    public function campaigns()
    {
        return $this->belongsToMany(\App\Campaign::class, 'affiliate_campaign', 'affiliate_id', 'campaign_id');
    }

    public function revenueTracker()
    {
        return $this->hasMany(AffiliateRevenueTracker::class);
    }

    public function affiliateRevenueTracker()
    {
        return $this->hasOne(AffiliateRevenueTracker::class, 'revenue_tracker_id', 'id');
    }

    public function oneRevenueTracker()
    {
        return $this->hasOne(AffiliateRevenueTracker::class);
    }

    public function scopeGetHandPAffiliates($query)
    {
        return $query->where('type', '=', 2);
    }

    public function scopeGetHandPActiveAffiliates($query)
    {
        return $query->where('type', '=', 2)->where('status', 1);
    }

    /**
     *   returns list of affiliates not affiliated to campaign.
     */
    public function scopeGetAvailableAffiliates($query, $id)
    {
        return $query->leftJoin('affiliate_campaign', function ($join) use ($id) {
            $join->on('affiliates.id', '=', 'affiliate_campaign.affiliate_id')
                ->where('affiliate_campaign.campaign_id', '=', $id);
        })
            ->whereNull('affiliate_campaign.id')
            ->where('status', 1)
            ->select('affiliates.id', DB::raw('CONCAT(affiliates.id, " - ",company) AS name'))
            ->orderBy('affiliates.id', 'asc');

        // $affiliated = AffiliateCampaign::where('campaign_id','=',$id)->lists('affiliate_id')->toArray();
        // return $query->select('affiliates.id', DB::raw('CONCAT(affiliates.id, " - ",company) AS name'))
        //         ->whereNotIn('id', $affiliated)
        //         ->where('status',1)
        //         ->orderBy('affiliates.id','asc');
    }

    /**
     *   returns list of affiliates not in Campaign Payout.
     */
    public function scopeGetAvailableAffiliatesForPayout($query, $id)
    {
        return $query->leftJoin('campaign_payouts', function ($join) use ($id) {
            $join->on('affiliates.id', '=', 'campaign_payouts.affiliate_id')
                ->where('campaign_payouts.campaign_id', '=', $id);
        })
            ->whereNull('campaign_payouts.id')
            ->where('status', 1)
            ->select('affiliates.id', DB::raw('CONCAT(affiliates.id, " - ",company) AS name'))
            ->orderBy('affiliates.id', 'asc');

        // $payouts = CampaignPayout::where('campaign_id','=',$id)->lists('affiliate_id')->toArray();
        // return $query->select('id', DB::raw('CONCAT(id, " - ",company) AS name'))
        //         ->whereNotIn('id', $payouts)
        //         ->where('status',1)
        //         ->orderBy('affiliates.id','asc');
    }

    public function scopeSearchAffiliates($query, $search, $start, $length)
    {
        $status = -1;
        //determine the status param
        if (strcasecmp($search, 'active') == 0) {
            $status = 1;
        } elseif (strcasecmp($search, 'inactive')) {
            $status = 0;
        }

        if (! empty($search) || $search != '') {
            $query->where('id', '=', $search)
                ->orWhere('company', 'like', '%'.$search.'%')
                ->orWhere('website_url', 'like', '%'.$search.'%')
                ->orWhere('phone', '=', $search)
                ->orWhere('address', 'like', '%'.$search.'%')
                ->orWhere('city', 'like', '%'.$search.'%')
                ->orWhere('state', 'like', '%'.$search.'%')
                ->orWhere('zip', '=', $search);
        }

        if ($status > 0) {
            $query->orWhere('status', '=', $status);
        }

        if ($start <= 0) {
            $query->take($length);
        } else {
            $query->take($start)->skip($length);
        }

        return $query;
    }

    public function affiliateCampaignRequest()
    {
        return $this->hasMany(AffiliateCampaignRequest::class);
    }

    public function scopeAffiliateCampaignSearch($query, $search, $start, $length, $order_col, $order_dir, $campaign, $status, $getCountOnly = false)
    {

        if (! $getCountOnly) {
            $query->leftJoin('affiliate_campaign', function ($join) use ($campaign) {
                $join->on('affiliate_campaign.affiliate_id', '=', 'affiliates.id')
                    ->where('affiliate_campaign.campaign_id', '=', $campaign);
            });

            $query->leftJoin('campaign_payouts', function ($join) use ($campaign) {
                $join->on('campaign_payouts.affiliate_id', '=', 'affiliates.id')
                    ->where('campaign_payouts.campaign_id', '=', $campaign);
            });
        }

        if ($status != null) {
            $query->where('affiliates.status', $status);
        }

        // $query->select(DB::raw('affiliates.id, company, affiliate_campaign.lead_cap_type,
        //     affiliate_campaign.lead_cap_value,
        //     CASE WHEN affiliate_campaign.lead_cap_type IS NULL THEN 0 ELSE 1 END as is_affiliate_campaign,
        //     received,payout'));

        if (! empty($search)) {
            $leadCapTypes = array_map('strtolower', config('constants.LEAD_CAP_TYPES'));

            if (is_numeric($search)) {
                if (floor($search) == $search) {
                    $query->orWhere('affiliates.id', 'like', '%'.$search.'%');
                }

                $query->orWhere('affiliate_campaign.lead_cap_value', $search);
            }

            $query->orWhere('affiliates.company', 'LIKE', '%'.$search.'%');

            $search_is_cap_type = array_search($search, $leadCapTypes);
            if ($search_is_cap_type !== false) {
                $query->orWhere('affiliate_campaign.lead_cap_type', $search_is_cap_type);
            }
        }

        if ($order_col != null && $order_dir != null) {
            if ($order_col > -1) {
                // $query->orderBy(DB::RAW('-affiliate_campaign.lead_cap_type'),'DESC');
                $query->orderBy('is_affiliate_campaign', 'DESC');
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
