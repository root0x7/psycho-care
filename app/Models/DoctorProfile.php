<?php

namespace App\Models;

use App\Enums\DoctorSpecialization;
use App\Enums\DoctorStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DoctorProfile extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = [
        'user_id', 'specialization', 'min_patient_age', 'bio',
        'tags', 'verification_status', 'rejection_reason',
        'submitted_at', 'reviewed_at', 'reviewed_by',
    ];

    protected $casts = [
        'tags'                => 'array',
        'specialization'      => DoctorSpecialization::class,
        'verification_status' => DoctorStatus::class,
        'submitted_at'        => 'datetime',
        'reviewed_at'         => 'datetime',
        'min_patient_age'     => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function experienceEntries(): HasMany
    {
        return $this->hasMany(DoctorExperienceEntry::class)->orderBy('sort_order');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['verification_status', 'rejection_reason'])->logOnlyDirty();
    }
}
