<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AffiliateCampaignRequest extends Model
{

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
