<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class CampaignFilterGroupFilter extends Model
{
    protected $connection;

    protected $fillable = [
        'campaign_filter_group_id',
        'filter_type_id',
        'value_text',
        'value_min_integer',
        'value_max_integer',
        'value_min_date',
        'value_max_date',
        'value_min_time',
        'value_max_time',
        'value_boolean',
        'value_array',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') == 'reports') {
            $this->connection = 'secondary';
        }
    }

    public function filter_group(): BelongsTo
    {
        return $this->belongsTo(\App\CampaignFilterGroup::class, 'campaign_filter_group_id', 'id');
    }

    public function filter_type(): BelongsTo
    {
        return $this->belongsTo(\App\FilterType::class);
    }
}
