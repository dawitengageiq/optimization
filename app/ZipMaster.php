<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ZipMaster extends Model
{
    protected $fillable = [
        'zip',
        'city',
        'state',
        'area_code',
        'time_zone',
    ];
}
