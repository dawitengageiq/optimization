<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LinkOutCount extends Model
{
    protected $connection;

    protected $table = 'link_out_counts';

    protected $fillable = [
        'campaign_id',
        'affiliate_id',
        'count',
        'expiration_date',
    ];

    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') == 'reports') {
            $this->connection = 'secondary';
        }
    }

    public function setAffiliateIdAttribute($value)
    {
        $this->attributes['affiliate_id'] = $value ?: null;
    }
}
