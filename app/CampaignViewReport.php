<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class CampaignViewReport extends Model
{
    protected $table = 'campaign_view_reports';

    protected $fillable = [
        'campaign_type_id',
        'campaign_id',
        'total_view_count',
        'current_view_count',
        'revenue_tracker_id',
        's1',
        's2',
        's3',
        's4',
        's5',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function campaignInfo()
    {
        return $this->hasone(Campaign::class, 'id', 'campaign_id');
    }

    public function affiliateCampaign()
    {
        return $this->hasMany(AffiliateCampaign::class, 'campaign_id', 'campaign_id');
    }

    public function getAffiliateCampaignRecordAttribute()
    {
        return DB::table('affiliate_campaign')
            ->select(DB::raw('count(id) as count'))
            ->where('affiliate_id', '=', $this->revenue_tracker_id)
            ->where('campaign_id', '=', $this->campaign_id)
            ->first();
    }
}
