<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\Locale;
use App\Enums\RegistrationStatus;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, HasRoles, LogsActivity, Notifiable, SoftDeletes;

    protected $fillable = [
        'telegram_id', 'telegram_nickname', 'telegram_username',
        'login', 'password',
        'first_name', 'second_name', 'third_name',
        'birth_date', 'gender', 'email', 'phone_number',
        'region', 'city', 'street_address',
        'latitude', 'longitude',
        'locale', 'timezone',
        'is_active', 'last_active_at', 'registration_status',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'birth_date'          => 'date',
        'last_active_at'      => 'datetime',
        'is_active'           => 'boolean',
        'gender'              => Gender::class,
        'locale'              => Locale::class,
        'registration_status' => RegistrationStatus::class,
        'latitude'            => 'decimal:7',
        'longitude'           => 'decimal:7',
    ];

    // Relations
    public function doctorProfile(): HasOne
    {
        return $this->hasOne(DoctorProfile::class);
    }

    public function patientProfile(): HasOne
    {
        return $this->hasOne(PatientProfile::class);
    }

    public function moodEntries(): HasMany
    {
        return $this->hasMany(MoodEntry::class);
    }

    public function moodReminderSlots(): HasMany
    {
        return $this->hasMany(MoodReminderSlot::class);
    }

    public function patientMoodScoreDescriptions(): HasMany
    {
        return $this->hasMany(PatientMoodScoreDescription::class);
    }

    public function testAttempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class);
    }

    public function telegramBotState(): HasOne
    {
        return $this->hasOne(TelegramBotState::class);
    }

    // Assignments as patient
    public function patientAssignments(): HasMany
    {
        return $this->hasMany(DoctorPatientAssignment::class, 'patient_id');
    }

    // Active doctor assignment
    public function activeAssignment(): HasOne
    {
        return $this->hasOne(DoctorPatientAssignment::class, 'patient_id')
            ->where('status', 'active')
            ->latestOfMany('assigned_at');
    }

    // Assignments as doctor
    public function doctorAssignments(): HasMany
    {
        return $this->hasMany(DoctorPatientAssignment::class, 'doctor_id');
    }

    public function patientConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'patient_id');
    }

    public function doctorConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'doctor_id');
    }

    // Filament
    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'superadmin' => $this->hasRole('superadmin'),
            'doctor'     => $this->hasRole('doctor'),
            'patient'    => $this->hasRole('patient'),
            default      => false,
        };
    }

    // Activity log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'email', 'is_active', 'registration_status'])
            ->logOnlyDirty();
    }

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([$this->first_name, $this->second_name, $this->third_name])));
    }
}
