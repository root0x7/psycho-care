<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeatherSnapshot extends Model
{
    protected $fillable = [
        'latitude', 'longitude', 'condition', 'temperature_celsius',
        'humidity_percent', 'raw_data', 'provider', 'recorded_at',
    ];

    protected $casts = [
        'raw_data'            => 'array',
        'recorded_at'         => 'datetime',
        'temperature_celsius' => 'decimal:2',
        'latitude'            => 'decimal:7',
        'longitude'           => 'decimal:7',
    ];

    public function moodEntries(): HasMany
    {
        return $this->hasMany(MoodEntry::class);
    }
}
