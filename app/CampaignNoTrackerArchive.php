<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
