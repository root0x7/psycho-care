<?php

namespace App\Policies;

use App\Models\RiskKeyword;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RiskKeywordPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'doctor']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'doctor']);
    }

    public function update(User $user, RiskKeyword $keyword): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }
        // Doctors can only edit pending (not yet approved) ones they created
        return $user->hasRole('doctor')
            && $keyword->created_by === $user->id
            && $keyword->status->value === 'pending';
    }

    public function delete(User $user, RiskKeyword $keyword): bool
    {
        return $user->hasRole('superadmin');
    }

    public function approve(User $user): bool
    {
        return $user->hasRole('superadmin');
    }
}
