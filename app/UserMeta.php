<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserMeta extends Model
{
    protected $connection;

    protected $table = 'user_metas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') == 'reports') {
            $this->connection = 'secondary';
        }
    }

    public function user()
    {
        $con = config('app.type') == 'reports' ? 'secondary' : 'mysql';

        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
