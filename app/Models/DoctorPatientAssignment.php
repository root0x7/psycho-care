<?php

namespace App\Models;

use App\Enums\AssignmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DoctorPatientAssignment extends Model
{
    protected $fillable = [
        'patient_id', 'doctor_id', 'status',
        'assigned_at', 'ended_at', 'notes',
    ];

    protected $casts = [
        'status'      => AssignmentStatus::class,
        'assigned_at' => 'datetime',
        'ended_at'    => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function conversation(): HasOne
    {
        return $this->hasOne(Conversation::class, 'assignment_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', AssignmentStatus::Active->value);
    }
}
