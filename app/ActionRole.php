<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActionRole extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'action_role';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role_id',
        'action_id',
        'permitted',
    ];
}
