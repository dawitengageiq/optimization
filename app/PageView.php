<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    protected $connection;

    protected $table = 'page_views';

    public $timestamps = false;

    protected $fillable = [
        'affiliate_id',
        'revenue_tracker_id',
        'type',
        's1',
        's2',
        's3',
        's4',
        's5',
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
