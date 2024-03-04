<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CampaignIp extends Model
{
    protected $table = 'campaign_ips';

    protected $fillable = [
        'campaign_id',
        'ip',
    ];

    public function campaign()
    {
        return $this->belongsTo(\App\Campaign::class);
    }
}
