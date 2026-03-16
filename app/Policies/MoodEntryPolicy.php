<?php

namespace App\Policies;

use App\Models\MoodEntry;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MoodEntryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'doctor', 'patient']);
    }

    public function view(User $user, MoodEntry $moodEntry): bool
    {
        if ($user->id === $moodEntry->user_id) {
            return true;
        }

        if ($user->hasRole('superadmin')) {
            return true;
        }

        // Assigned doctor only
        if ($user->hasRole('doctor')) {
            return $user->doctorAssignments()
                ->where('patient_id', $moodEntry->user_id)
                ->where('status', 'active')
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('patient');
    }

    public function update(User $user, MoodEntry $moodEntry): bool
    {
        return $user->id === $moodEntry->user_id;
    }

    public function delete(User $user, MoodEntry $moodEntry): bool
    {
        return $user->hasRole('superadmin');
    }
}
