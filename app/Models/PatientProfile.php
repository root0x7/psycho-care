<?php

namespace App\Models;

use App\Enums\MaritalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'biography', 'marital_status',
        'family_psychiatric_history', 'childhood_trauma',
        'somatic_disease_history', 'somatic_disease_details',
        'suicide_history', 'suicide_who',
        'impulse_control_disorders',
        'trauma_orphanage', 'trauma_prison', 'trauma_sexual_violence',
        'drug_use_history', 'gambling_addiction',
        'subjective_addiction', 'subjective_addiction_details',
        'psychosomatic_disorder',
        'previous_psychological_treatment', 'psychological_treatment_details',
        'intake_completed', 'intake_completed_at',
    ];

    protected $casts = [
        'marital_status'                     => MaritalStatus::class,
        'family_psychiatric_history'         => 'boolean',
        'childhood_trauma'                   => 'boolean',
        'somatic_disease_history'            => 'boolean',
        'suicide_history'                    => 'boolean',
        'impulse_control_disorders'          => 'boolean',
        'trauma_orphanage'                   => 'boolean',
        'trauma_prison'                      => 'boolean',
        'trauma_sexual_violence'             => 'boolean',
        'drug_use_history'                   => 'boolean',
        'gambling_addiction'                 => 'boolean',
        'subjective_addiction'               => 'boolean',
        'psychosomatic_disorder'             => 'boolean',
        'previous_psychological_treatment'   => 'boolean',
        'intake_completed'                   => 'boolean',
        'intake_completed_at'                => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
