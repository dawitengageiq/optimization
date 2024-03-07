<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class CampaignPartnerPayout extends Model
{

    protected $fillable = [
        'campaign_id',
        'affiliate_id',
        'amount',
        'effective_date',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(\App\Campaign::class);
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(\App\Affiliate::class);
    }
}
