<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignIp extends Model
{
    protected $fillable = [
        'campaign_id',
        'ip',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(\App\Campaign::class);
    }
}
