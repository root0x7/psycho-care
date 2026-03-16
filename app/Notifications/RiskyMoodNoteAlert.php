<?php

namespace App\Notifications;

use App\Models\MoodEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class RiskyMoodNoteAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly MoodEntry $moodEntry,
        public readonly Collection $matches
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $patient = $this->moodEntry->user;
        $highestMatch = $this->matches
            ->sortByDesc(fn ($m) => $m['severity']->value)
            ->first();
        $highestSeverityLabel = $highestMatch ? $highestMatch['severity']->label() : 'Unknown';

        return (new MailMessage)
            ->subject("[PsychoCare Alert] Risk detected in patient note")
            ->line("A risk keyword was detected in a mood note from patient: {$patient?->full_name}")
            ->line("Severity: {$highestSeverityLabel}")
            ->line("Mood Score: {$this->moodEntry->score}/9")
            ->line("Note excerpt: " . mb_substr($this->moodEntry->note ?? '', 0, 200))
            ->action('View Patient', url('/doctor/patients/' . $patient?->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'risky_mood_note',
            'mood_entry_id'  => $this->moodEntry->id,
            'patient_id'     => $this->moodEntry->user_id,
            'match_count'    => $this->matches->count(),
            'max_severity'   => $this->matches->max(fn ($m) => $m['severity']->value),
        ];
    }
}
