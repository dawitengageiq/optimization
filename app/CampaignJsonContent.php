<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CampaignJsonContent extends Model
{
    protected $connection;

    protected $table = 'campaign_json_contents';

    protected $fillable = [
        'id',
        'json',
        'script',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') == 'reports') {
            $this->connection = 'secondary';
        }
    }
}
