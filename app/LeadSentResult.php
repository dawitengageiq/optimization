<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeadSentResult extends Model
{
    protected $table = 'lead_sent_results';

    protected $fillable = [
        'id',
        'value',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
