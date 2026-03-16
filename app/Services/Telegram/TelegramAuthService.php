<?php

namespace App\Services\Telegram;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class TelegramAuthService
{
    /**
     * Verify Telegram Login Widget data.
     *
     * @see https://core.telegram.org/widgets/login#checking-authorization
     */
    public function verify(array $data): bool
    {
        $botToken = config('services.telegram.bot_token');

        if (empty($botToken)) {
            Log::warning('Telegram bot token not configured');
            return false;
        }

        $hash = $data['hash'] ?? '';
        unset($data['hash']);

        // Build check string
        ksort($data);
        $checkString = implode("\n", array_map(
            fn($k, $v) => "{$k}={$v}",
            array_keys($data),
            array_values($data)
        ));

        $secretKey = hash('sha256', $botToken, true);
        $expectedHash = hash_hmac('sha256', $checkString, $secretKey);

        if (!hash_equals($expectedHash, $hash)) {
            return false;
        }

        // Check freshness (5 minutes)
        $authDate = (int) ($data['auth_date'] ?? 0);
        if (time() - $authDate > 300) {
            return false;
        }

        return true;
    }

    public function findOrCreateUser(array $telegramData): ?User
    {
        return User::withTrashed()
            ->where('telegram_id', (string) $telegramData['id'])
            ->first();
    }
}
