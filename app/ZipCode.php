<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ZipCode extends Model
{
    protected $table = 'zip_codes';

    protected $fillable = [
        'zip',
        'city',
        'state',
    ];

    public $timestamps = false;
}
