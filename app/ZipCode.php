<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ZipCode extends Model
{

    protected $fillable = [
        'zip',
        'city',
        'state',
    ];

    public $timestamps = false;
}
