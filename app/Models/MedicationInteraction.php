<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicationInteraction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'drug_a_id', 'drug_b_id', 'is_safe', 'description',
        'severity', 'source', 'source_fetched_at',
    ];

    protected $casts = [
        'is_safe'           => 'boolean',
        'source_fetched_at' => 'datetime',
    ];

    public function drugA(): BelongsTo
    {
        return $this->belongsTo(Medication::class, 'drug_a_id');
    }

    public function drugB(): BelongsTo
    {
        return $this->belongsTo(Medication::class, 'drug_b_id');
    }
}
