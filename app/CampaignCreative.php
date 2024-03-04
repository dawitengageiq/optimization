<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CampaignCreative extends Model
{
    protected $connection;

    protected $table = 'campaign_creatives';

    protected $fillable = [
        'weight',
        'campaign_id',
        'image',
        'description',
    ];

    public function campaign()
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
