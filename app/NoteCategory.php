<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NoteCategory extends Model
{
    protected $connection;

    protected $fillable = [
        'name',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') == 'reports') {
            $this->connection = 'secondary';
        }
    }
}
