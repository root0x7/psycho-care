<?php

namespace App\Models;

use App\Enums\DoctorExperienceType;
use App\Enums\EducationDegree;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorExperienceEntry extends Model
{
    protected $fillable = [
        'doctor_profile_id', 'type', 'degree', 'institution',
        'course_name', 'place', 'role', 'years_raw',
        'start_year', 'end_year', 'sort_order',
    ];

    protected $casts = [
        'type'       => DoctorExperienceType::class,
        'degree'     => EducationDegree::class,
        'start_year' => 'integer',
        'end_year'   => 'integer',
        'sort_order' => 'integer',
    ];

    public function doctorProfile(): BelongsTo
    {
        return $this->belongsTo(DoctorProfile::class);
    }

    public function getYearsDisplayAttribute(): string
    {
        if ($this->start_year && $this->end_year && $this->start_year !== $this->end_year) {
            return "{$this->start_year}–{$this->end_year}";
        }
        return (string) ($this->start_year ?? $this->years_raw ?? '');
    }
}
