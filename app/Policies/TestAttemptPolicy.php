<?php

namespace App\Policies;

use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TestAttemptPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'doctor', 'patient']);
    }

    public function view(User $user, TestAttempt $attempt): bool
    {
        if ($user->id === $attempt->user_id) {
            return true;
        }

        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->hasRole('doctor')) {
            return $user->doctorAssignments()
                ->where('patient_id', $attempt->user_id)
                ->where('status', 'active')
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('patient');
    }

    public function update(User $user, TestAttempt $attempt): bool
    {
        return $user->id === $attempt->user_id && $attempt->status->value === 'in_progress';
    }

    public function delete(User $user, TestAttempt $attempt): bool
    {
        return $user->hasRole('superadmin');
    }
}
