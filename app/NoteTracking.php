<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NoteTracking extends Model
{
    protected $connection;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'note_id',
    ];
}
