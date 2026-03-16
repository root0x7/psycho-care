<?php

namespace App\Jobs;

use App\Models\MoodEntry;
use App\Services\Weather\WeatherService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AttachWeatherToMoodEntry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(public readonly MoodEntry $moodEntry) {}

    public function handle(WeatherService $weatherService): void
    {
        $user = $this->moodEntry->user;

        if (!$user || !$user->latitude || !$user->longitude) {
            return;
        }

        $snapshot = $weatherService->getSnapshot(
            (float) $user->latitude,
            (float) $user->longitude
        );

        if ($snapshot) {
            $this->moodEntry->update(['weather_snapshot_id' => $snapshot->id]);
        }
    }
}
