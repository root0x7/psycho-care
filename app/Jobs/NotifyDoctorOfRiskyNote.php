<?php

namespace App\Jobs;

use App\Models\MoodEntry;
use App\Notifications\RiskyMoodNoteAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyDoctorOfRiskyNote implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly MoodEntry $moodEntry) {}

    public function handle(): void
    {
        $patient = $this->moodEntry->user;

        if (!$patient) {
            return;
        }

        // Find active assignment
        $assignment = $patient->activeAssignment()->with('doctor')->first();

        if (!$assignment?->doctor) {
            return;
        }

        $matches = $this->moodEntry->riskKeywordMatches()
            ->where('doctor_notified', false)
            ->get();

        if ($matches->isEmpty()) {
            return;
        }

        $assignment->doctor->notify(new RiskyMoodNoteAlert($this->moodEntry, $matches));

        $matches->each(function ($match) {
            $match->update([
                'doctor_notified' => true,
                'notified_at'     => now(),
            ]);
        });
    }
}
