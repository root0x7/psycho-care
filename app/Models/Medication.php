<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medication extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'generic_name', 'drug_class', 'description', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function switchesFrom(): HasMany
    {
        return $this->hasMany(MedicationSwitch::class, 'drug_from_id');
    }

    public function switchesTo(): HasMany
    {
        return $this->hasMany(MedicationSwitch::class, 'drug_to_id');
    }

    public function combinationsAsPrimary(): HasMany
    {
        return $this->hasMany(MedicationCombination::class, 'primary_drug_id');
    }

    public function taperGuide(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MedicationTaperGuide::class);
    }

    public function pregnancyRisk(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MedicationPregnancyRisk::class);
    }

    public function interactionsAsA(): HasMany
    {
        return $this->hasMany(MedicationInteraction::class, 'drug_a_id');
    }

    public function interactionsAsB(): HasMany
    {
        return $this->hasMany(MedicationInteraction::class, 'drug_b_id');
    }
}
