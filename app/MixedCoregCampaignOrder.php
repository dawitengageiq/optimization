<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MixedCoregCampaignOrder extends Model
{
    protected $table = 'mixed_coreg_campaign_orders';

    protected $fillable = [
        'revenue_tracker_id',
        'campaign_id_order',
        'reorder_reference_date',
    ];

    public $timestamps = false;

    public function affiliateRevenueTracker()
    {
        return $this->hasOne(AffiliateRevenueTracker::class, 'revenue_tracker_id', 'revenue_tracker_id');
    }
}
