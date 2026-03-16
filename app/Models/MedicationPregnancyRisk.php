<?php

namespace App\Models;

use App\Enums\MedicationRiskLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicationPregnancyRisk extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'medication_id', 'risk_level', 'description',
        'source', 'source_fetched_at',
    ];

    protected $casts = [
        'risk_level'        => MedicationRiskLevel::class,
        'source_fetched_at' => 'datetime',
    ];

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }
}
