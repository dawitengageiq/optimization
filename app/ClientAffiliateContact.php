<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientAffiliateContact extends Model
{
    protected $table = 'client_affiliate_contacts';

    protected $fillable = [
        'user_id',
        'affiliate_id',
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function affiliate()
    {
        return $this->belongsTo(\App\Affiliate::class);
    }
}
