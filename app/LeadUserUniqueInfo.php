<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeadUserUniqueInfo extends Model
{
    protected $connection;

    protected $table = 'lead_users_uniqueinfo';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'email_category',
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
        if (config('app.type') != 'reports') {
            $this->connection = 'secondary';
        }
    }
}
