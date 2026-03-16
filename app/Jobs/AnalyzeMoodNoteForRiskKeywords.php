<?php

namespace App\Jobs;

use App\Models\MoodEntry;
use App\Services\Risk\RiskKeywordDetectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeMoodNoteForRiskKeywords implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(public readonly MoodEntry $moodEntry) {}

    public function handle(RiskKeywordDetectionService $detectionService): void
    {
        if (empty($this->moodEntry->note)) {
            return;
        }

        $matches = $detectionService->analyze($this->moodEntry);

        if ($matches->isNotEmpty()) {
            Log::info('Risk keywords detected in mood entry', [
                'mood_entry_id' => $this->moodEntry->id,
                'match_count'   => $matches->count(),
            ]);

            // Dispatch notification to assigned doctor
            \App\Jobs\NotifyDoctorOfRiskyNote::dispatch($this->moodEntry);
        }
    }
}
