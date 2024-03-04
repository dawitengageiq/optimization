<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CplChecker extends Model
{
    protected $connection;

    public $timestamps = false;

    protected $fillable = [
        'campaign_id',
        'created_at',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') == 'reports') {
            $this->connection = 'secondary';
        }
    }
}
