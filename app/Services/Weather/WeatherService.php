<?php

namespace App\Services\Weather;

use App\Models\WeatherSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    private const CACHE_TTL_MINUTES = 30;
    private const STALE_THRESHOLD_MINUTES = 60;

    public function getSnapshot(float $latitude, float $longitude): ?WeatherSnapshot
    {
        try {
            $cacheKey = "weather:{$latitude}:{$longitude}";

            $snapshotId = Cache::remember($cacheKey, Carbon::now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($latitude, $longitude) {
                return $this->fetchAndStore($latitude, $longitude)?->id;
            });

            return $snapshotId ? WeatherSnapshot::find($snapshotId) : null;
        } catch (\Throwable $e) {
            Log::warning('Weather fetch failed', ['error' => $e->getMessage()]);
            return null; // Graceful failure
        }
    }

    private function fetchAndStore(float $latitude, float $longitude): ?WeatherSnapshot
    {
        // Check for recent enough snapshot in DB first
        $existing = WeatherSnapshot::where('latitude', $latitude)
            ->where('longitude', $longitude)
            ->where('recorded_at', '>=', Carbon::now()->subMinutes(self::STALE_THRESHOLD_MINUTES))
            ->latest('recorded_at')
            ->first();

        if ($existing) {
            return $existing;
        }

        // Use Open-Meteo (free, no API key required)
        $response = Http::timeout(5)->get('https://api.open-meteo.com/v1/forecast', [
            'latitude'  => $latitude,
            'longitude' => $longitude,
            'current_weather' => true,
            'hourly'    => 'relativehumidity_2m',
        ]);

        if (!$response->ok()) {
            return null;
        }

        $data = $response->json();
        $current = $data['current_weather'] ?? [];

        return WeatherSnapshot::create([
            'latitude'            => $latitude,
            'longitude'           => $longitude,
            'condition'           => $this->mapWeatherCode($current['weathercode'] ?? null),
            'temperature_celsius' => $current['temperature'] ?? null,
            'humidity_percent'    => $data['hourly']['relativehumidity_2m'][0] ?? null,
            'raw_data'            => $data,
            'provider'            => 'open-meteo',
            'recorded_at'         => Carbon::now('UTC'),
        ]);
    }

    private function mapWeatherCode(?int $code): string
    {
        if ($code === null) {
            return 'unknown';
        }
        return match (true) {
            $code === 0             => 'sunny',
            in_array($code, [1, 2]) => 'partly_cloudy',
            $code === 3             => 'overcast',
            in_array($code, [51, 53, 55, 61, 63, 65, 80, 81, 82]) => 'rainy',
            in_array($code, [71, 73, 75, 77, 85, 86]) => 'snowy',
            in_array($code, [95, 96, 99]) => 'thunderstorm',
            default                 => 'cloudy',
        };
    }
}
