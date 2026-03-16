<?php

namespace App\Providers;

use App\Events\DoctorVerificationStatusChanged;
use App\Events\MoodEntryCreated;
use App\Listeners\ProcessMoodEntryAfterCreation;
use App\Listeners\SendDoctorVerificationNotification;
use App\Models\DoctorProfile;
use App\Models\MoodEntry;
use App\Models\PatientProfile;
use App\Models\RiskKeyword;
use App\Models\TestAttempt;
use App\Policies\DoctorProfilePolicy;
use App\Policies\MoodEntryPolicy;
use App\Policies\PatientProfilePolicy;
use App\Policies\RiskKeywordPolicy;
use App\Policies\TestAttemptPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register model policies
        Gate::policy(MoodEntry::class, MoodEntryPolicy::class);
        Gate::policy(TestAttempt::class, TestAttemptPolicy::class);
        Gate::policy(PatientProfile::class, PatientProfilePolicy::class);
        Gate::policy(DoctorProfile::class, DoctorProfilePolicy::class);
        Gate::policy(RiskKeyword::class, RiskKeywordPolicy::class);

        // Register event listeners
        Event::listen(MoodEntryCreated::class, ProcessMoodEntryAfterCreation::class);
        Event::listen(DoctorVerificationStatusChanged::class, SendDoctorVerificationNotification::class);
    }
}
