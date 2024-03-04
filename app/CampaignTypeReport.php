<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CampaignTypeReport extends Model
{
    protected $table = 'campaign_type_reports';

    protected $fillable = [
        'campaign_type_id',
        'revenue_tracker_id',
        'session',
        's1',
        's2',
        's3',
        's4',
        's5',
    ];

    public function revenueTracker()
    {
        return $this->belongsTo(AffiliateRevenueTracker::class, 'revenue_tracker_id', 'revenue_tracker_id');
    }
}
