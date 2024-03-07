<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FilterType extends Model
{

    protected $fillable = [
        'type',
        'name',
        'status',
    ];
}
