<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientAdvertiserContact extends Model
{
    protected $table = 'client_advertiser_contacts';

    protected $fillable = [
        'user_id',
        'advertiser_id',
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function advertiser()
    {
        return $this->belongsTo(\App\Advertiser::class);
    }
}
