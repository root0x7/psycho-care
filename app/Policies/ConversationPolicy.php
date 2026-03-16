<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConversationPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Conversation $conversation): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->id === $conversation->patient_id
            || $user->id === $conversation->doctor_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['patient', 'doctor']);
    }

    public function sendMessage(User $user, Conversation $conversation): bool
    {
        return $user->id === $conversation->patient_id
            || $user->id === $conversation->doctor_id;
    }
}
