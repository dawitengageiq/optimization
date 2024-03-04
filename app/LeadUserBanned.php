<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeadUserBanned extends Model
{
    protected $connection;

    protected $table = 'lead_users_banned';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') == 'reports') {
            $this->connection = 'secondary';
        }
    }

    public function scopeSearchLeads($query, $params, $start, $length, $order_col, $order_dir)
    {
        if (isset($params['id']) && $params['id'] !== '') {
            $query->where('id', $params['id']);
        }

        if (isset($params['email']) && $params['email'] !== '') {
            $query->where('email', $params['email']);
        }

        if (isset($params['first_name']) && $params['first_name'] !== '') {
            $query->where('first_name', $params['first_name']);
        }

        if (isset($params['last_name']) && $params['last_name'] !== '') {
            $query->where('last_name', $params['last_name']);
        }

        if (isset($params['phone']) && $params['phone'] !== '') {
            $query->where('phone', $params['phone']);
        }

        if (isset($params['search']['value']) && $params['search']['value'] !== '') {
            $search = $params['search']['value'];
            $query->where('email', 'LIKE', '%'.$search.'%');
            $query->orWhere('first_name', 'LIKE', '%'.$search.'%');
            $query->orWhere('last_name', 'LIKE', '%'.$search.'%');
            $query->orWhere('phone', 'LIKE', '%'.$search.'%');
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
