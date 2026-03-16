<?php

namespace App\Models;

use App\Enums\RiskLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskKeywordMatch extends Model
{
    protected $fillable = [
        'mood_entry_id', 'risk_keyword_id', 'matched_text',
        'offset_start', 'offset_end', 'severity',
        'doctor_notified', 'notified_at',
    ];

    protected $casts = [
        'severity'         => RiskLevel::class,
        'doctor_notified'  => 'boolean',
        'notified_at'      => 'datetime',
        'offset_start'     => 'integer',
        'offset_end'       => 'integer',
    ];

    public function moodEntry(): BelongsTo
    {
        return $this->belongsTo(MoodEntry::class);
    }

    public function riskKeyword(): BelongsTo
    {
        return $this->belongsTo(RiskKeyword::class);
    }
}
