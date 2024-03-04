<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AffiliateCampaignRequest extends Model
{
    protected $table = 'affiliate_campaign_requests';

    protected $fillable = [
        'campaign_id',
        'affiliate_id',
        'status',
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
