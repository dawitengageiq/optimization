<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CampaignRevenueBreakdown extends Model
{
    protected $connection;

    public $timestamps = false;

    protected $fillable = [
        'campaign_id',
        'records',
        'revenue',
        'created_at',
        'average_revenue',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') != 'reports') {
            $this->connection = 'secondary';
        }
    }
}
