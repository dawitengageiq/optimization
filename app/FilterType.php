<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FilterType extends Model
{
    protected $table = 'filter_types';

    protected $fillable = [
        'type',
        'name',
        'status',
    ];
}
