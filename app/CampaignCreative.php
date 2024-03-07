<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignCreative extends Model
{
    protected $connection;

    protected $fillable = [
        'weight',
        'campaign_id',
        'image',
        'description',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') == 'reports') {
            $this->connection = 'secondary';
        }
    }
}
