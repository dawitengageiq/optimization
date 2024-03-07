<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CampaignTypeOrder extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'revenue_tracker_id',
        'campaign_type_id',
        'campaign_id_order',
        'reorder_reference_date',
    ];

    public function affiliateRevenueTracker(): HasOne
    {
        return $this->hasOne(AffiliateRevenueTracker::class, 'revenue_tracker_id', 'revenue_tracker_id');
    }
}
