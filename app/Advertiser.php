<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Advertiser extends Model
{
    protected $connection;

    protected $table = 'advertisers';

    protected $fillable = [
        'company',
        'website_url',
        'id',
        'phone',
        'address',
        'city',
        'state',
        'zip',
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

    public function user()
    {
        return $this->hasOne(\App\User::class, 'advertiser_id', 'id');
    }

    public function scopeSearchAdvertisers($query, $search, $start, $length)
    {
        $status = -1;
        //determine the status param
        if (strcasecmp($search, 'active') == 0) {
            $status = 1;
        } elseif (strcasecmp($search, 'inactive')) {
            $status = 0;
        }

        if (! empty($search) || $search != '') {
            $query->where('id', '=', $search)
                ->orWhere('company', 'like', '%'.$search.'%')
                ->orWhere('website_url', 'like', '%'.$search.'%')
                ->orWhere('phone', '=', $search)
                ->orWhere('address', 'like', '%'.$search.'%')
                ->orWhere('city', 'like', '%'.$search.'%')
                ->orWhere('state', 'like', '%'.$search.'%')
                ->orWhere('zip', '=', $search);
        }

        if ($status > 0) {
            $query->orWhere('status', '=', $status);
        }

        if ($start <= 0) {
            $query->take($length);
        } else {
            $query->take($start)->skip($length);
        }

        return $query;
    }
}
