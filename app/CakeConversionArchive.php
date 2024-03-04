<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CakeConversionArchive extends Model
{
    protected $table = 'cake_conversions_archive';

    protected $fillable = [
        'id',
        'visitor_id',
        'request_session_id',
        'click_request_session_id',
        'click_id',
        'conversion_date',
        'last_updated',
        'click_date',
        'event_id',
        'affiliate_id',
        'advertiser_id',
        'offer_id',
        'offer_name',
        'campaign_id',
        'creative_id',
        'sub_id_1',
        'sub_id_2',
        'sub_id_3',
        'sub_id_4',
        'sub_id_5',
        'conversion_ip_address',
        'click_ip_address',
        'received_amount',
        'test',
        'transaction_id',
    ];
}
