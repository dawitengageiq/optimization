<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    public function scopeGetAllNames($query)
    {
        return $query->select('name')
            ->orderBy('name', 'asc');
    }

    public function scopeGetAllActiveNames($query)
    {
        return $query->select('name')
            ->where('status', '=', 1)
            ->orderBy('name', 'asc');
    }
}
