<?php

namespace App\Policies;

use App\Models\DoctorProfile;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DoctorProfilePolicy
{
    use HandlesAuthorization;

    public function view(User $user, DoctorProfile $profile): bool
    {
        return true; // Doctor profiles are public-facing
    }

    public function update(User $user, DoctorProfile $profile): bool
    {
        return $user->id === $profile->user_id || $user->hasRole('superadmin');
    }

    public function review(User $user): bool
    {
        return $user->hasRole('superadmin');
    }
}
