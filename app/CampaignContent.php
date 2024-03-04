<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CampaignContent extends Model
{
    protected $connection;

    protected $table = 'campaign_contents';

    protected $fillable = [
        'id',
        'content',
        'name',
        'deal',
        'description',
        'fields',
        'additional_fields',
        'rules',
        'sticker',
        'cpa_creative_id',

    ];

    protected $casts = [
        'fields' => 'array',
        'additional_fields' => 'array',
        'rules' => 'array',
    ];

    public function campaign()
    {
        return $this->belongsTo(\App\Campaign::class, 'id', 'id');
    }

    public function campaign_type()
    {
        return $this->belongsTo(\App\Campaign::class, 'id', 'id')->select(['id', 'campaign_type']);
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') == 'reports') {
            $this->connection = 'secondary';
        }
    }
}
