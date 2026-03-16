<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MoodReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        // Sent via Telegram bot channel primarily
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'mood_reminder',
            'message' => 'Time to record your mood! How are you feeling right now?',
        ];
    }
}
