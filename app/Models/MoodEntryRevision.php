<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoodEntryRevision extends Model
{
    protected $fillable = [
        'mood_entry_id', 'previous_score', 'previous_note',
        'new_score', 'new_note', 'channel', 'revised_at',
    ];

    protected $casts = [
        'previous_score' => 'integer',
        'new_score'      => 'integer',
        'revised_at'     => 'datetime',
    ];

    public function moodEntry(): BelongsTo
    {
        return $this->belongsTo(MoodEntry::class);
    }
}
