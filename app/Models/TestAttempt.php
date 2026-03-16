<?php

namespace App\Models;

use App\Enums\TestAttemptStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestAttempt extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'test_id', 'test_version', 'test_snapshot',
        'status', 'total_score', 'max_score',
        'started_at', 'completed_at', 'duration_seconds',
    ];

    protected $casts = [
        'test_snapshot'    => 'array',
        'status'           => TestAttemptStatus::class,
        'total_score'      => 'integer',
        'max_score'        => 'integer',
        'started_at'       => 'datetime',
        'completed_at'     => 'datetime',
        'duration_seconds' => 'integer',
        'test_version'     => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(TestAttemptAnswer::class);
    }

    public function sectionSummaries(): HasMany
    {
        return $this->hasMany(TestAttemptSectionSummary::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', TestAttemptStatus::Completed->value);
    }
}
