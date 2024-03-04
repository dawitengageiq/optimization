<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CampaignRejectionStatistic extends Model
{
    protected $connection;

    public $timestamps = false;

    protected $fillable = [
        'campaign_id',
        'total_count',
        'reject_count',
        'duplicate_count',
        'filter_count',
        'prepop_count',
        'other_count',
        'created_at',
        'acceptable_reject_count',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') != 'reports') {
            $this->connection = 'secondary';
        }
    }
}
