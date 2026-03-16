<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestAttemptSectionSummary extends Model
{
    protected $fillable = [
        'test_attempt_id', 'test_section_id', 'score', 'max_score',
        'interpretation_label', 'interpretation_description',
    ];

    protected $casts = [
        'score'     => 'integer',
        'max_score' => 'integer',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(TestAttempt::class, 'test_attempt_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(TestSection::class, 'test_section_id');
    }
}
