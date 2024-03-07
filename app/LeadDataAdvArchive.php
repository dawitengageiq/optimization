<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class LeadDataAdvArchive extends Model
{
    protected $table = 'lead_data_advs_archive';

    protected $fillable = [
        'id',
        'value',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(LeadArchive::class);
    }
}
