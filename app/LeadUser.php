<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeadUser extends Model
{
    protected $connection;

    protected $table = 'lead_users';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'birthdate',
        'gender',
        'zip',
        'city',
        'state',
        'address1',
        'address2',
        'ethnicity',
        'phone',
        'source_url',
        'affiliate_id',
        'revenue_tracker_id',
        's1',
        's2',
        's3',
        's4',
        's5',
        'ip',
        'is_mobile',
        'status',
        'response',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') == 'reports') {
            $this->connection = 'secondary';
        }
    }

    public function scopeSurveyTakersFeed($query)
    {
        $query->select('first_name', 'last_name', 'address1', 'city', 'state', 'zip', 'birthdate', 'created_at', 'phone', 'email');

        $query->where(function ($subQuery) {
            $subQuery->where('first_name', '!=', '');
            $subQuery->whereNotNull('first_name');
        });

        $query->where(function ($subQuery) {
            $subQuery->where('last_name', '!=', '');
            $subQuery->whereNotNull('last_name');
        });

        $query->where(function ($subQuery) {
            $subQuery->where('address1', '!=', '');
            $subQuery->whereNotNull('address1');
        });

        $query->where(function ($subQuery) {
            $subQuery->where('city', '!=', '');
            $subQuery->whereNotNull('city');
        });

        $query->where(function ($subQuery) {
            $subQuery->where('state', '!=', '');
            $subQuery->whereNotNull('state');
        });

        $query->where(function ($subQuery) {
            $subQuery->where('zip', '!=', '');
            $subQuery->whereNotNull('zip');
        });

        $query->where(function ($subQuery) {
            $subQuery->where('birthdate', '!=', '');
            $subQuery->whereNotNull('birthdate');
        });

        $query->where(function ($subQuery) {
            $subQuery->where('created_at', '!=', '');
            $subQuery->whereNotNull('created_at');
        });

        $query->where(function ($subQuery) {
            $subQuery->where('phone', '!=', '');
            $subQuery->whereNotNull('phone');
        });

        $query->where(function ($subQuery) {
            $subQuery->where('email', '!=', '');
            $subQuery->whereNotNull('email');
        });

        return $query;
    }

    public function scopeSearchUsers($query, $params)
    {
        if (isset($params['id']) && $params['id'] !== '') {
            $query->where('id', $params['id']);
        }

        if (isset($params['email']) && $params['email'] !== '') {
            $query->where('email', $params['email']);
        }

        if (isset($params['gender']) && $params['gender'] !== '') {
            $query->where('gender', $params['gender']);
        }

        if (isset($params['affiliate_id']) && $params['affiliate_id'] !== '') {
            $query->where('affiliate_id', $params['affiliate_id']);
        }

        if (isset($params['revenue_tracker']) && $params['revenue_tracker'] !== '') {
            $query->where('revenue_tracker_id', $params['revenue_tracker']);
        }

        if (isset($params['first_name']) && $params['first_name'] !== '') {
            $query->where('first_name', $params['first_name']);
        }

        if (isset($params['last_name']) && $params['last_name'] !== '') {
            $query->where('last_name', $params['last_name']);
        }

        if (isset($params['zip']) && $params['zip'] !== '') {
            $query->where('zip', $params['zip']);
        }

        if (isset($params['city']) && $params['city'] !== '') {
            $query->where('city', $params['city']);
        }

        if (isset($params['state']) && $params['state'] !== '') {
            $query->where('state', $params['state']);
        }

        if (isset($params['phone']) && $params['phone'] !== '') {
            $query->where('phone', $params['phone']);
        }

        if (isset($params['ip']) && $params['ip'] !== '') {
            $query->where('ip', $params['ip']);
        }

        if (isset($params['source_url']) && $params['source_url'] !== '') {
            $query->where('source_url', 'like', '%'.$params['source_url'].'%');
        }

        if ((isset($params['date_from']) && $params['date_from'] !== '') &&
            (isset($params['date_to']) && $params['date_to'] !== '')) {
            $query->whereRaw('created_at >= ? and created_at <= ?',
                [
                    $params['date_from'].' 00:00:00',
                    $params['date_to'].' 23:59:59',
                ]);
        }

        if (isset($params['search']['value']) && $params['search']['value'] !== '') {
            $search = $params['search']['value'];
            $filter = $params['filter'];

            switch ($filter) {
                case 'affiliate_id':

                    //check if the search term is integer
                    if (is_numeric($search)) {
                        $query->where('affiliate_id', $search);
                    } else {
                        //this is because affiliate_id field is integer only
                        $query->where('affiliate_id', -1);
                    }

                    break;
                case 'revenue_tracker_id':

                    //check if the search term is integer
                    if (is_numeric($search)) {
                        $query->where('revenue_tracker_id', $search);
                    } else {
                        //this is because revenue_tracker_id field is integer only
                        $query->where('revenue_tracker_id', -1);
                    }

                    break;
                case 'email':
                    $query->where('email', 'like', '%'.$search.'%');
                    break;
                case 'name':
                    $query->where('first_name', 'like', '%'.$search.'%');
                    $query->orWhere('last_name', 'like', '%'.$search.'%');
                    break;
                case 'city':
                    $query->where('city', 'like', '%'.$search.'%');
                    break;
                case 'state':
                    $query->where('state', 'like', '%'.$search.'%');
                    break;
                case 'zip':
                    $query->where('zip', $search);
                    break;
                case 'gender':
                    if ($search == 'Female' || $search == 'F') {
                        $gender = 'F';
                    } elseif ($search == 'Male' || $search == 'M') {
                        $gender = 'M';
                    } else {
                        $gender = '';
                    }
                    $query->where('gender', $gender);
                    break;
                case 'source_url':
                    $query->where('source_url', 'like', '%'.$search.'%');
                    break;
                case 'created_at':
                    $query->where('created_at', 'like', '%'.$search.'%');
                    break;
                default:
                    $query->where('id', $search);
                    break;
            }
        }

        if (isset($params['s1']) && $params['s1'] !== '') {
            $query->where('s1', '=', $params['s1']);
        }

        if (isset($params['s2']) && $params['s2'] !== '') {
            $query->where('s2', '=', $params['s2']);
        }

        if (isset($params['s3']) && $params['s3'] !== '') {
            $query->where('s3', '=', $params['s3']);
        }

        if (isset($params['s4']) && $params['s4'] !== '') {
            $query->where('s4', '=', $params['s4']);
        }

        if (isset($params['s5']) && $params['s5'] !== '') {
            $query->where('s5', '=', $params['s5']);
        }

        return $query;
    }
}
