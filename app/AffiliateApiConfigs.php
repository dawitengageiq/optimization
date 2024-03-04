<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AffiliateApiConfigs extends Model
{
    protected $table = 'affiliate_api_configs';

    protected $fillable = [
        'affiliate_id',
        'campaign_type_order',
        'excluded_campaign_id',
        'display_limit',
    ];
}
