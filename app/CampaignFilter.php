<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CampaignFilter extends Model
{
    protected $table = 'campaign_filters';

    protected $fillable = [
        'campaign_id',
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

    public function campaign()
    {
        return $this->belongsTo(\App\Campaign::class);
    }

    public function filter_type()
    {
        return $this->belongsTo(\App\FilterType::class);
    }
}
