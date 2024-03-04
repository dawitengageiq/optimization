<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CampaignConfig extends Model
{
    protected $connection;

    protected $table = 'campaign_configs';

    protected $fillable = [
        'id',
        'post_url',
        'post_header',
        'post_data',
        'post_data_fixed_value',
        'post_data_map',
        'post_method',
        'post_success',
        'ping_url',
        'ping_success',
        'ping_header',
        'ftp_sent',
        'ftp_protocol',
        'ftp_username',
        'ftp_password',
        'ftp_host',
        'ftp_port',
        'ftp_timeout',
        'ftp_directory',
        'email_sent',
        'email_to',
        'email_title',
        'email_body',
    ];

    public function campaign()
    {
        return $this->belongsTo(\App\Campaign::class, 'id', 'id');
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') == 'reports') {
            $this->connection = 'secondary';
        }
    }
}
