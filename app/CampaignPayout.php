<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CampaignPayout extends Model
{
    protected $connection;

    protected $table = 'campaign_payouts';

    protected $fillable = [
        'campaign_id',
        'affiliate_id',
        'received',
        'payout',
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
        return $this->belongsTo(Campaign::class);
    }

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function scopeGetCampaignAffiliatePayout($query, $campaignID, $affiliateID)
    {
        return $query->where('campaign_id', '=', $campaignID)
            ->where('affiliate_id', '=', $affiliateID);
    }
}
