<?php

namespace App\Policies;

use App\Models\PatientProfile;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PatientProfilePolicy
{
    use HandlesAuthorization;

    public function view(User $user, PatientProfile $profile): bool
    {
        if ($user->id === $profile->user_id) {
            return true;
        }

        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->hasRole('doctor')) {
            return $user->doctorAssignments()
                ->where('patient_id', $profile->user_id)
                ->where('status', 'active')
                ->exists();
        }

        return false;
    }

    public function update(User $user, PatientProfile $profile): bool
    {
        return $user->id === $profile->user_id || $user->hasRole('superadmin');
    }
}
