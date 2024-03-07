<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AffiliateApiConfigs extends Model
{

    protected $fillable = [
        'affiliate_id',
        'campaign_type_order',
        'excluded_campaign_id',
        'display_limit',
    ];
}
