<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignTypeView extends Model
{
    protected $fillable = [
        'campaign_type_id',
        'revenue_tracker_id',
        'session',
        'timestamp',
    ];

    public $timestamps = false;

    public function revenueTracker(): BelongsTo
    {
        return $this->belongsTo(AffiliateRevenueTracker::class, 'revenue_tracker_id', 'revenue_tracker_id');
    }
}
