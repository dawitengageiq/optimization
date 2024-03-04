<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeadUserRequest extends Model
{
    protected $connection;

    protected $table = 'lead_user_request';

    protected $fillable = [
        'request_type',
        'email',
        'first_name',
        'last_name',
        'state',
        'city',
        'zip',
        'address',
        'request_date',
        'subscribed_campaigns',
        'is_removed', //STATUS
        'is_sent',
        'is_deleted',
        'is_reported',
        'phone_number',
    ];

    //is_removed = status = 1 -> sent; 2 -> deleted; 3 -> reported
    /*
    sent: 1,
    deleted: 2,
    reported: 3
    */

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') == 'reports') {
            $this->connection = 'secondary';
        }
    }
}
