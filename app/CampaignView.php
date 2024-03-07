<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignView extends Model
{
    protected $connection;

    protected $fillable = [
        'campaign_id',
        'affiliate_id',
        'creative_id',
        'path_id',
        'session',
        's1',
        's2',
        's3',
        's4',
        's5',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') == 'reports') {
            $this->connection = 'secondary';
        }
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function scopeCreativeRevenueReport($query, $params)
    {

        if (isset($params['campaign_id']) && $params['campaign_id'] !== '') {
            $query->where('campaign_id', '=', $params['campaign_id']);
        }

        if (isset($params['campaign_ids']) && count($params['campaign_ids']) > 0) {
            $query->whereIn('campaign_id', $params['campaign_ids']);
        }

        if (isset($params['affiliate_id']) && $params['affiliate_id'] !== '') {
            $query->where('affiliate_id', '=', $params['affiliate_id']);
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
            $query->whereRaw('date(created_at) = date(?)', [$params['lead_date_from']]);
        } elseif ((! isset($params['lead_date_from']) || $params['lead_date_from'] === '') &&
                (isset($params['lead_date_to']) && $params['lead_date_to'] !== '')) {
            $query->whereRaw('date(created_at) = date(?)', [$params['lead_date_to']]);
        } else {
            $query->whereRaw('date(created_at) = ?', [$params['lead_date_today']]);
        }

        // order by create date
        $query->orderByDesc('created_at');

        $query->groupBy('campaign_id', 'creative_id', 'path_id', 'affiliate_id');

        return $query;
    }
}
