<?php

namespace App\Models;

use App\Enums\MoodChannel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MoodEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'score', 'note', 'channel',
        'submitted_at', 'user_timezone', 'local_submitted_at',
        'weather_snapshot_id', 'context_metadata',
        'normalized_score', 'was_overwritten',
    ];

    protected $casts = [
        'score'            => 'integer',
        'channel'          => MoodChannel::class,
        'submitted_at'     => 'datetime',
        'local_submitted_at' => 'datetime',
        'context_metadata' => 'array',
        'normalized_score' => 'decimal:2',
        'was_overwritten'  => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function weatherSnapshot(): BelongsTo
    {
        return $this->belongsTo(WeatherSnapshot::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(MoodEntryRevision::class);
    }

    public function riskKeywordMatches(): HasMany
    {
        return $this->hasMany(RiskKeywordMatch::class);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
