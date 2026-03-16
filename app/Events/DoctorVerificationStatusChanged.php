<?php

namespace App\Events;

use App\Models\DoctorProfile;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DoctorVerificationStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly DoctorProfile $doctorProfile) {}
}
