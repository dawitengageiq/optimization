<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeadMessage extends Model
{
    protected $table = 'lead_messages';

    protected $fillable = [
        'id',
        'value',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
