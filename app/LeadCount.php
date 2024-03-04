<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeadCount extends Model
{
    protected $connection;

    protected $fillable = [
        'campaign_id',
        'affiliate_id',
        'count',
        'reference_date',
    ];

    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') == 'reports') {
            $this->connection = 'secondary';
        }
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function scopeGetCount($query, $params)
    {
        /*
        if(isset($params['campaign_id']))
        {
            $query->where('campaign_id','=',$params['campaign_id']);
        }

        if(isset($params['affiliate_id']))
        {
            $query->where('affiliate_id','=',$params['affiliate_id']);
        }
        */

        if (isset($params['campaign_id']) && isset($params['affiliate_id'])) {
            $query->where('campaign_id', '=', $params['campaign_id'])
                ->where('affiliate_id', '=', $params['affiliate_id']);
        } elseif (isset($params['campaign_id']) && ! isset($params['affiliate_id'])) {
            $query->where('campaign_id', '=', $params['campaign_id'])
                ->whereRaw('affiliate_id IS NULL');
        }

        return $query;
    }
}
