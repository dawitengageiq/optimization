<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
    ];

    public function actions()
    {
        return $this->belongsToMany(Action::class)->withPivot('id', 'role_id', 'action_id', 'permitted');
    }
}
