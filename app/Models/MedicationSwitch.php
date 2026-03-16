<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicationSwitch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'drug_from_id', 'drug_to_id', 'switch_type',
        'stopping_doses', 'starting_doses', 'notes',
        'source', 'source_fetched_at',
    ];

    protected $casts = ['source_fetched_at' => 'datetime'];

    public function drugFrom(): BelongsTo
    {
        return $this->belongsTo(Medication::class, 'drug_from_id');
    }

    public function drugTo(): BelongsTo
    {
        return $this->belongsTo(Medication::class, 'drug_to_id');
    }
}
