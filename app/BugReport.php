<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BugReport extends Model
{
    use SoftDeletes;

    protected $connection;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'bug_reports';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reporter_name',
        'reporter_email',
        'bug_summary',
        'bug_description',
        'evidences',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') != 'reports') {
            $this->connection = 'secondary';
        }
    }
}
