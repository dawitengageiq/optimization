<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OfferGoesDown extends Model
{
    protected $connection;

    protected $fillable = [
        'campaign_id',
        'affiliates',
        'revenue',
        'date',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') == 'reports') {
            $this->connection = 'secondary';
        }
    }
}
