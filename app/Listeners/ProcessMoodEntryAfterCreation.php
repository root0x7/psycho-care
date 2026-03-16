<?php

namespace App\Listeners;

use App\Events\MoodEntryCreated;
use App\Jobs\AnalyzeMoodNoteForRiskKeywords;
use App\Jobs\AttachWeatherToMoodEntry;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessMoodEntryAfterCreation implements ShouldQueue
{
    public function handle(MoodEntryCreated $event): void
    {
        $entry = $event->moodEntry;

        // Attach weather context
        AttachWeatherToMoodEntry::dispatch($entry);

        // Analyze note for risk keywords
        if (!empty($entry->note)) {
            AnalyzeMoodNoteForRiskKeywords::dispatch($entry);
        }
    }
}
