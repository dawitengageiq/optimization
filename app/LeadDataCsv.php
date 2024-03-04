<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeadDataCsv extends Model
{
    protected $table = 'lead_data_csvs';

    protected $fillable = [
        'id',
        'value',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
