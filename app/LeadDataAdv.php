<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeadDataAdv extends Model
{
    protected $table = 'lead_data_advs';

    protected $fillable = [
        'id',
        'value',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
