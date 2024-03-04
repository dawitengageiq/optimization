<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $connection;

    protected $table = 'campaigns';

    protected $fillable = [
        'name',
        'advertiser_id',
        'status',
        'description',
        'notes',
        'image',
        'lead_cap_type',
        'lead_cap_value',
        'default_received',
        'default_payout',
        'priority',
        'campaign_type',
        'category_id',
        'rate',
        'linkout_offer_id',
        'olr_program_id',
        'publisher_name',
        'is_external',
        'advertiser_email',
        'status_dupe', //status prior to inactive
        'linkout_cake_status',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') == 'reports') {
            $this->connection = 'secondary';
        }
    }

    public function payouts()
    {
        return $this->belongsToMany(Affiliate::class, 'campaign_payouts', 'campaign_id', 'affiliate_id')->withPivot('received');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function advertiser()
    {
        return $this->belongsTo(Advertiser::class);
    }

    public function affiliates()
    {
        return $this->belongsToMany(Affiliate::class, 'affiliate_campaign', 'campaign_id', 'affiliate_id');
    }

    public function affiliateCampaign()
    {
        return $this->hasMany(AffiliateCampaign::class);
    }

    public function firstAffiliateCampaign()
    {
        return $this->hasOne(AffiliateCampaign::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    /*
    public function leadCounts()
    {
        return $this->hasMany(LeadCount::class);
    }
    */
    public function filters()
    {
        return $this->hasMany(CampaignFilter::class);
    }

    public function config()
    {
        return $this->hasOne(CampaignConfig::class, 'id', 'id');
    }

    public function content()
    {
        return $this->hasOne(CampaignContent::class, 'id', 'id');
    }

    public function noTracker()
    {
        return $this->hasOne(CampaignNoTracker::class, 'campaign_id');
    }

    public function linkOutCounts()
    {
        return $this->hasOne(LinkOutCount::class, 'campaign_id');
    }

    public function leadCounts()
    {
        if (! in_array($this->campaign_type, [4, 5, 6])) {
            return $this->hasMany(LeadCount::class, 'campaign_id');
        } else {
            return;
        }
    }

    public function creatives()
    {
        return $this->hasMany(CampaignCreative::class, 'campaign_id');
    }

    public function scopeGetExternals($query)
    {
        return $query->where('name', 'like', 'External%')
            ->orderBy('name', 'ASC');
    }

    public function scopeActiveCampaigns($query, $param = null)
    {
        //$query->select('id','name','lead_cap_value AS cap_value');
        $query->select(DB::raw("id, name, lead_cap_value AS cap_value, (CASE WHEN lead_cap_type = 0 THEN 'Unlimited' WHEN lead_cap_type = 1 THEN 'Daily' WHEN lead_cap_type = 2 THEN 'Weekly' WHEN lead_cap_type = 3 THEN 'Monthly' WHEN lead_cap_type = 4 THEN 'Yearly' END) AS cap_type"));

        $query->where('status', '!=', 0);

        if ($param != null && isset($param['affiliate_id'])) {
            $query->where('id', '=', DB::raw('(SELECT affiliate_campaign.campaign_id FROM affiliate_campaign WHERE affiliate_campaign.affiliate_id='.$param['affiliate_id'].')'));
        } elseif ($param != null && isset($param['advertiser_id'])) {
            $query->where('id', '=', DB::raw('(SELECT campaigns.id FROM campaigns WHERE campaigns.advertiser_id='.$param['advertiser_id'].')'));
        }

        $query->orderBy('id', 'asc');
    }

    public function filter_groups()
    {
        return $this->hasMany(CampaignFilterGroup::class)->where('status', 1);
    }

    public function affiliateCampaignRequest()
    {
        return $this->hasMany(AffiliateCampaignRequest::class);
    }

    public function scopeGetCapDetails($query, $campaignID)
    {
        return $query->select('id', 'lead_cap_type', 'lead_cap_value')
            ->where('id', '=', $campaignID);
    }

    public function scopeSearchCampaigns($query, $search, $start, $length, $order_col, $order_dir, $filters = null)
    {
        $query->join('advertisers', 'campaigns.advertiser_id', '=', 'advertisers.id');

        if ($filters != null && isset($filters['json_content']) && $filters['json_content'] != '') {
            $query->leftJoin('campaign_json_contents', 'campaigns.id', '=', 'campaign_json_contents.id');

            if ($filters['json_content'] == 'true') {
                $query->where(function ($q) {
                    $q->where('campaign_json_contents.json', '!=', '')
                        ->orWhere('campaign_json_contents.script', '!=', '');
                });
            } elseif ($filters['json_content'] == 'false') {
                // $query->whereNull('campaign_json_contents.id')->orWhere(function ($q) {
                //     $q->where('campaign_json_contents.json', '=', '')
                //         ->where('campaign_json_contents.script', '=', '');
                // });

                $query->where(function ($q) {
                    $q->whereNull('campaign_json_contents.id')->orWhere(function ($qq) {
                        $qq->where('campaign_json_contents.json', '=', '')
                            ->where('campaign_json_contents.script', '=', '');
                    });
                });
            }
        }

        if (! empty($search)) {
            $leadCapTypes = array_map('strtolower', config('constants.LEAD_CAP_TYPES'));
            $campaignStatuses = array_map('strtolower', config('constants.CAMPAIGN_STATUS'));
            $campaignTypes = array_map('strtolower', config('constants.CAMPAIGN_TYPES'));

            $query->where(function ($q) use ($search, $leadCapTypes, $campaignStatuses, $campaignTypes) {
                if (is_numeric($search)) {
                    if (floor($search) == $search) {
                        $q->where('campaigns.priority', $search);
                        $q->orWhere('campaigns.id', $search);
                        $q->orWhere('campaigns.lead_cap_value', $search);
                    } else {
                        $q->where('campaigns.lead_cap_value', $search);
                    }
                    $q->orWhere('campaigns.default_received', $search);

                    $q->orWhere('advertisers.company', 'LIKE', '%'.$search.'%');
                } else {
                    $q->where('advertisers.company', 'LIKE', '%'.$search.'%');
                }

                $q->orWhere('campaigns.name', 'LIKE', '%'.$search.'%');

                $search_is_cap_type = array_search(strtolower($search), $leadCapTypes);
                if ($search_is_cap_type !== false) {
                    $q->orWhere('campaigns.lead_cap_type', $search_is_cap_type);
                }

                $search_is_campaign_status = array_search(strtolower($search), $campaignStatuses);
                if ($search_is_campaign_status !== false) {
                    $q->orWhere('campaigns.status', '=', $search_is_campaign_status);
                }

                $search_maybe_campaign_type = [];
                foreach ($campaignTypes as $id => $type) {
                    if (stripos($type, strtolower($search)) !== false) {
                        $search_maybe_campaign_type[] = $id;
                    }
                }
                if (count($search_maybe_campaign_type) > 0) {
                    $q->orWhere(function ($qq) use ($search_maybe_campaign_type) {
                        $qq->whereIn('campaigns.campaign_type', $search_maybe_campaign_type);
                    });
                }
            });

        }

        if ($filters != null && isset($filters['show_inactive']) && $filters['show_inactive'] != '') {
            if ($filters['show_inactive'] != 1) {
                $query->where('campaigns.status', '!=', 0);
            }
        }

        if ($order_col != null && $order_dir != null) {
            if ($order_col > -1) {
                $query->orderBy($order_col, $order_dir);
            }

            if ($filters != null && isset($filters['show_inactive']) && $filters['show_inactive'] != '') {
                // $query->orderByRaw('FIELD(campaigns.status, 2, 1, 3, 0)');
                $query->orderBy('campaigns.status', 'ASC');
            }
        } else {
            if ($filters != null && isset($filters['show_inactive']) && $filters['show_inactive'] != '') {
                // $query->orderByRaw('FIELD(campaigns.status, 2, 1, 3, 0)');
                $query->orderBy('campaigns.status', 'ASC');
                $query->orderBy('campaigns.priority', 'ASC');
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

    public function scopeCampaignAffiliateManagementTable($query, $search, $start, $length, $order_col, $order_dir, $affiliate, $operation)
    {
        if ($operation == 0) {
            $query->join('affiliate_campaign', function ($join) use ($affiliate) {
                $join->on('campaigns.id', '=', 'affiliate_campaign.campaign_id')
                    ->where('affiliate_campaign.affiliate_id', '=', $affiliate)
                    ->where('campaigns.id', '!=', env('EIQ_IFRAME_ID', 0));
            });
        } else {
            $query->leftJoin('affiliate_campaign', function ($join) use ($affiliate) {
                $join->on('campaigns.id', '=', 'affiliate_campaign.campaign_id')
                    ->where('affiliate_campaign.affiliate_id', '=', $affiliate)
                    ->where('campaigns.id', '!=', env('EIQ_IFRAME_ID', 0));
            });
        }

        if (! empty($search)) {
            $leadCapTypes = array_map('strtolower', config('constants.LEAD_CAP_TYPES'));
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

            $search_is_cap_type = array_search($search, $leadCapTypes);
            if ($search_is_cap_type !== false) {
                $query->orWhere('affiliate_campaign.lead_cap_type', $search_is_cap_type);
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

        if ($order_col != null && $order_dir != null) {
            if ($order_col > -1) {
                $query->orderBy(DB::RAW('-affiliate_campaign.lead_cap_type'), 'DESC');
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

    public function scopeGetFilterFreeCampaigns($query, $campaign_type, $affiliate_id, $limit = null)
    {

        $query
            ->select(DB::RAW('campaigns.id, campaigns.status, campaigns.lead_cap_type, campaigns.lead_cap_value, affiliate_campaign.id as affiliate_campaign_exists, affiliate_campaign.lead_cap_type as aff_cap_type, affiliate_campaign.lead_cap_value as aff_cap_value, (SELECT COUNT(*) FROM campaign_filter_groups WHERE status = 1 AND campaign_id = campaigns.id AND (SELECT COUNT(*) FROM campaign_filter_group_filters WHERE campaign_filter_group_filters.campaign_filter_group_id = campaign_filter_groups.id) > 0) as total_filters, CASE WHEN campaigns.lead_cap_type != 0 THEN (SELECT count FROM lead_counts WHERE lead_counts.campaign_id = campaigns.id AND affiliate_id IS NULL) ELSE NULL END as campaign_lead_count, CASE WHEN affiliate_campaign.lead_cap_type != 0 THEN (SELECT count FROM lead_counts WHERE lead_counts.campaign_id = campaigns.id AND affiliate_id = '.$affiliate_id.') ELSE NULL END as campaign_affiliate_lead_count,(SELECT id FROM campaign_creatives WHERE campaign_id = campaigns.id AND weight != 0 ORDER BY RAND() LIMIT 1) as creative_id'))
            ->leftJoin('affiliate_campaign', function ($join) use ($affiliate_id) {
                $join->on('affiliate_campaign.campaign_id', '=', 'campaigns.id')
                    ->where('affiliate_campaign.affiliate_id', '=', $affiliate_id);
            });

        if (is_array($campaign_type)) {
            $query->whereIn('campaign_type', $campaign_type);
        } else {
            $query->where('campaign_type', $campaign_type);
        }

        $query
            ->whereIn('campaigns.status', [1, 2])
            ->having('total_filters', '=', 0)
            ->havingRaw('((status = 2) OR (status = 1 AND affiliate_campaign_exists IS NOT NULL))')
            ->havingRaw('((lead_cap_type = 0) OR (lead_cap_type != 0 AND lead_cap_value > campaign_lead_count))')
            ->havingRaw('((aff_cap_type IS NULL) OR (aff_cap_type = 0) OR (aff_cap_value > campaign_affiliate_lead_count))')
            ->orderBy('campaigns.priority', 'ASC');

        if ($limit != null) {
            $query->take($limit);
        }

        return $query;

        // SELECT
        // campaigns.id, campaigns.status, campaigns.lead_cap_type, campaigns.lead_cap_value,
        // affiliate_campaign.id as affiliate_campaign_exists, affiliate_campaign.lead_cap_type as aff_cap_type, affiliate_campaign.lead_cap_value as aff_cap_value,
        // (SELECT id FROM campaign_creatives WHERE campaign_id = campaigns.id AND weight != 0 ORDER BY RAND() LIMIT 1) as creative_id,
        // (SELECT COUNT(*) FROM campaign_filter_groups WHERE status = 1 AND campaign_id = campaigns.id AND
        //     (SELECT COUNT(*) FROM campaign_filter_group_filters WHERE campaign_filter_group_filters.campaign_filter_group_id = campaign_filter_groups.id) > 0) as total_filters,
        // CASE WHEN campaigns.lead_cap_type != 0 THEN (SELECT count FROM lead_counts WHERE lead_counts.campaign_id = campaigns.id AND affiliate_id IS NULL) ELSE NULL END as campaign_lead_count,
        // CASE WHEN affiliate_campaign.lead_cap_type != 0 THEN (SELECT count FROM lead_counts WHERE lead_counts.campaign_id = campaigns.id AND affiliate_id = 7612) ELSE NULL END as campaign_affiliate_lead_count
        // FROM campaigns
        // LEFT JOIN  affiliate_campaign ON affiliate_campaign.campaign_id = campaigns.id AND affiliate_campaign.affiliate_id = 7612
        // WHERE campaign_type = 1 AND campaigns.status IN (1, 2)
        // HAVING total_filters = 0 AND
        // ((status = 2) OR (status = 1 AND affiliate_campaign_exists IS NOT NULL)) AND
        // ((lead_cap_type = 0) OR (lead_cap_type != 0 AND lead_cap_value > campaign_lead_count)) AND
        // ((aff_cap_type IS NULL) OR (aff_cap_type = 0) OR (aff_cap_value > campaign_affiliate_lead_count))
        // ORDER BY priority LIMIT 5

    }
}
