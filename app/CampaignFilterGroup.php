<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class CampaignFilterGroup extends Model
{
    protected $connection;

    protected $fillable = [
        'campaign_id',
        'name',
        'description',
        'status',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') == 'reports') {
            $this->connection = 'secondary';
        }
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(\App\Campaign::class, 'campaign_id', 'id');
    }

    public function filters(): HasMany
    {
        return $this->hasMany(CampaignFilterGroupFilter::class);
    }
}
