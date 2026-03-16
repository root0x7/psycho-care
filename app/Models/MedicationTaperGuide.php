<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicationTaperGuide extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'medication_id', 'tapering_schedule', 'notes',
        'source', 'source_fetched_at',
    ];

    protected $casts = ['source_fetched_at' => 'datetime'];

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }
}
