<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignPostingInstruction extends Model
{
    protected $fillable = [
        'id',
        'sample_code',
        'posting_instruction',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(\App\Campaign::class, 'id', 'id');
    }
}
