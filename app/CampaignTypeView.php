<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CampaignTypeView extends Model
{
    protected $table = 'campaign_type_views';

    protected $fillable = [
        'campaign_type_id',
        'revenue_tracker_id',
        'session',
        'timestamp',
    ];

    public $timestamps = false;

    public function revenueTracker()
    {
        return $this->belongsTo(AffiliateRevenueTracker::class, 'revenue_tracker_id', 'revenue_tracker_id');
    }
}
