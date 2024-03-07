<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function actions(): BelongsToMany
    {
        return $this->belongsToMany(Action::class)->withPivot('id', 'role_id', 'action_id', 'permitted');
    }
}
