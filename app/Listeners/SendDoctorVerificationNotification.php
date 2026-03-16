<?php

namespace App\Listeners;

use App\Events\DoctorVerificationStatusChanged;
use App\Enums\DoctorStatus;
use App\Notifications\DoctorVerificationApproved;
use App\Notifications\DoctorVerificationRejected;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendDoctorVerificationNotification implements ShouldQueue
{
    public function handle(DoctorVerificationStatusChanged $event): void
    {
        $profile = $event->doctorProfile;
        $doctor  = $profile->user;

        if (!$doctor) {
            return;
        }

        match ($profile->verification_status) {
            DoctorStatus::Approved => $doctor->notify(new DoctorVerificationApproved()),
            DoctorStatus::Rejected => $doctor->notify(new DoctorVerificationRejected($profile->rejection_reason)),
            default => null,
        };
    }
}
