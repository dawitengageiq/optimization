<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeadSentResult extends Model
{

    protected $fillable = [
        'id',
        'value',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
