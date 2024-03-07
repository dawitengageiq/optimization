<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Action extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'code',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
