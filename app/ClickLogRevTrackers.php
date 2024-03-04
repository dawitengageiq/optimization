<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClickLogRevTrackers extends Model
{
    protected $connection;

    protected $table = 'click_log_rev_trackers';

    protected $fillable = [
        'affiliate_id',
        'revenue_tracker_id',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') != 'reports') {
            $this->connection = 'secondary';
        }
    }

    public function revenue_tracker()
    {
        return $this->belongsTo(AffiliateRevenueTracker::class, 'revenue_tracker_id', 'revenue_tracker_id');
    }

    public function scopeSearchModel($query, $search, $start, $length, $order_col, $order_dir)
    {
        if (! empty($search)) {

            $query->where('revenue_tracker_id', 'LIKE', '%'.$search.'%');
            $query->orWhere('affiliate_id', 'LIKE', '%'.$search.'%');
        }

        if ($order_col != null && $order_dir != null) {
            if ($order_col > -1) {
                $query->orderBy($order_col, $order_dir);
            }
        }

        if ($start != null) {
            $query->skip($start);
        }

        if ($length != null) {
            $query->take($length);
        }

        return $query;
    }
}
