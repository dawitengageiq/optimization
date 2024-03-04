<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CampaignPostingInstruction extends Model
{
    protected $table = 'campaign_posting_instructions';

    protected $fillable = [
        'id',
        'sample_code',
        'posting_instruction',
    ];

    public function campaign()
    {
        return $this->belongsTo(\App\Campaign::class, 'id', 'id');
    }
}
