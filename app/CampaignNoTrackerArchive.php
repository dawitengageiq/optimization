<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class CampaignNoTrackerArchive extends Model
{

    protected $fillable = [
        'campaign_id',
        'email',
        'count',
        'last_session',
        'created_up',
        'updated_at',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
