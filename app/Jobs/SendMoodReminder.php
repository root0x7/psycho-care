<?php

namespace App\Jobs;

use App\Models\MoodReminderSlot;
use App\Notifications\MoodReminderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMoodReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 30;

    public function __construct(public readonly MoodReminderSlot $slot) {}

    public function handle(): void
    {
        $user = $this->slot->user;

        if (!$user || !$user->is_active) {
            return;
        }

        // Skip if telegram_id is missing (can't send bot message)
        if (empty($user->telegram_id)) {
            return;
        }

        try {
            $user->notify(new MoodReminderNotification());
        } catch (\Throwable $e) {
            // Gracefully handle Telegram delivery failure
            \Illuminate\Support\Facades\Log::warning('Mood reminder delivery failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
