<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PathSpeed extends Model
{
    protected $connection;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'path',
        'sum_time',
        'avg_time',
        'up_time',
        'created_at',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') != 'reports') {
            $this->connection = 'secondary';
        }
    }
}
