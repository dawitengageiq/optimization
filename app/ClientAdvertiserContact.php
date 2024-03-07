<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ClientAdvertiserContact extends Model
{

    protected $fillable = [
        'user_id',
        'advertiser_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\User::class);
    }

    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(\App\Advertiser::class);
    }
}
