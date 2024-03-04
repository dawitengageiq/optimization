<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CampaignNoTrackerArchive extends Model
{
    protected $table = 'campaign_no_tracker_archives';

    protected $fillable = [
        'campaign_id',
        'email',
        'count',
        'last_session',
        'created_up',
        'updated_at',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
