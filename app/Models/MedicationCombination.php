<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicationCombination extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'primary_drug_id', 'adding_drug_id',
        'main_information', 'starting_doses', 'precautions',
        'source', 'source_fetched_at',
    ];

    protected $casts = ['source_fetched_at' => 'datetime'];

    public function primaryDrug(): BelongsTo
    {
        return $this->belongsTo(Medication::class, 'primary_drug_id');
    }

    public function addingDrug(): BelongsTo
    {
        return $this->belongsTo(Medication::class, 'adding_drug_id');
    }
}
