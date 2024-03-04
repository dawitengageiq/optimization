<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CronHistory extends Model
{
    protected $fillable = [
        'id',
        'leads_queued',
        'leads_processed',
        'leads_waiting',
        'time_started',
        'time_ended',
        'status',
        'lead_ids',
    ];

    public $timestamps = false;
}
