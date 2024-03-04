<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CampaignPartnerPayout extends Model
{
    protected $table = 'campaign_partner_payouts';

    protected $fillable = [
        'campaign_id',
        'affiliate_id',
        'amount',
        'effective_date',
    ];

    public function campaign()
    {
        return $this->belongsTo(\App\Campaign::class);
    }

    public function affiliate()
    {
        return $this->belongsTo(\App\Affiliate::class);
    }
}
