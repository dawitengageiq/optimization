<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AffiliateWebsite extends Model
{
    protected $connection;

    /**
     * Table
     */
    protected $table = 'affiliate_websites';

    /**
     * Editable fields
     */
    protected $fillable = [
        'affiliate_id',
        'website_name',
        'website_description',
        'payout',
        'status',
        'revenue_tracker_id',
        'allow_datafeed',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') == 'reports') {
            $this->connection = 'secondary';
        }
    }

    /**
     * Reltionship
     */
    public function view()
    {
        return $this->hasMany(WebsitesViewTracker::class, 'id', 'website_id');
    }

    /**
     * Reltionship
     */
    public function dupe()
    {
        return $this->hasMany(WebsitesViewTrackerDuplicate::class, 'website_id', 'id');
    }

    public function scopeSearchWebsites($query, $affiliate, $search, $start, $length, $order_col, $order_dir)
    {
        $query->where('affiliate_id', $affiliate);

        if (! empty($search)) {
            $query->where('website_name', $search);
            $query->orWhere('website_description', $search);

            if (is_numeric($search)) {
                $query->orWhere('payout', $search);
            }
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

    public function scopeGetAffiliateWebsites($query, $affiliate_id)
    {
        $query->where('affiliate_id', $affiliate_id);
    }
}
