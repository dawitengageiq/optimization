<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Path extends Model
{
    protected $table = 'paths';

    protected $fillable = [
        'name',
        'url',
    ];
}
