<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class LeadDuplicate extends Model
{
    protected $fillable = [
        'campaign_id',
        'affiliate_id',
        's1',
        's2',
        's3',
        's4',
        's5',
        'lead_status',
        'lead_email',
        'received',
        'payout',
        'retry_count',
        'last_retry_date',
        'path_id',
        'creative_id',
    ];

    public function setCreativeIdAttribute($value)
    {
        $this->attributes['creative_id'] = $value ?: null;
    }

    public function setPathIdAttribute($value)
    {
        $this->attributes['path_id'] = $value ?: null;
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function scopeSearchLeads($query, $params)
    {
        if (isset($params['campaign_id']) && $params['campaign_id'] !== '') {
            $query->where('campaign_id', '=', $params['campaign_id']);
        }

        if (isset($params['affiliate_id']) && $params['affiliate_id'] !== '') {
            $query->where('affiliate_id', '=', $params['affiliate_id']);
        }

        if (isset($params['lead_status']) && $params['lead_status'] !== '') {
            $query->where('lead_status', '=', $params['lead_status']);
        }

        if ((isset($params['lead_date_from']) && $params['lead_date_from'] !== '') &&
            (isset($params['lead_date_to']) && $params['lead_date_to'] !== '')) {
            $query->whereRaw('date(created_at) >= date(?) and date(created_at) <= date(?)',
                [
                    $params['lead_date_from'],
                    $params['lead_date_to'],
                ]);
        } elseif ((isset($params['lead_date_from']) && $params['lead_date_from'] !== '') &&
                (! isset($params['lead_date_to']) || $params['lead_date_to'] === '')) {
            $query()->whereRaw('date(created_at) = date(?)', [$params['lead_date_from']]);
        } elseif ((! isset($params['lead_date_from']) || $params['lead_date_from'] === '') &&
                (isset($params['lead_date_to']) && $params['lead_date_to'] !== '')) {
            $query()->whereRaw('date(created_at) = date(?)', [$params['lead_date_to']]);
        }

        if (isset($params['duplicate']) && $params['duplicate'] !== '') {
            if ($params['duplicate'] == 1) {
                $query->having(DB::raw('COUNT(*)'), '=', 1);
            } else {
                $query->having(DB::raw('COUNT(*)'), '>', 1);
            }
        }

        //order by create date
        $query->orderBy('created_at', 'desc');

        if (isset($params['limit_rows']) && $params['limit_rows'] !== '') {
            $query->take($params['limit_rows']);
        }

        return $query;
    }
}
