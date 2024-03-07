<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadSentResultArchive extends Model
{
    protected $table = 'lead_sent_results_archive';

    protected $fillable = [
        'id',
        'value',
        'created_at',
        'updated_at',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(LeadArchive::class);
    }
}
