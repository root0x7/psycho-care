<?php

namespace App\Actions\Auth;

use App\Enums\RegistrationStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterViaTelegramAction
{
    /**
     * Register or link a user via Telegram Login Widget data.
     *
     * Assumption: Telegram auth data has already been verified by the caller
     * using the HMAC-SHA256 check against bot token.
     */
    public function execute(array $telegramData, string $role = 'patient'): User
    {
        $telegramId = (string) $telegramData['id'];

        return DB::transaction(function () use ($telegramData, $telegramId, $role) {
            $user = User::withTrashed()->where('telegram_id', $telegramId)->first();

            if ($user) {
                if ($user->trashed()) {
                    $user->restore();
                }
                $user->update([
                    'telegram_nickname' => $telegramData['username'] ?? null,
                    'telegram_username' => $telegramData['username'] ?? null,
                    'first_name'        => $telegramData['first_name'] ?? $user->first_name,
                    'second_name'       => $telegramData['last_name'] ?? $user->second_name,
                ]);
                return $user;
            }

            $user = User::create([
                'telegram_id'         => $telegramId,
                'telegram_nickname'   => $telegramData['username'] ?? null,
                'telegram_username'   => $telegramData['username'] ?? null,
                'first_name'          => $telegramData['first_name'] ?? 'User',
                'second_name'         => $telegramData['last_name'] ?? null,
                'registration_status' => RegistrationStatus::Pending->value,
                'is_active'           => true,
            ]);

            $user->assignRole($role);

            return $user;
        });
    }
}
