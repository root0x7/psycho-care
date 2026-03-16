<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestAttemptAnswer extends Model
{
    protected $fillable = [
        'test_attempt_id', 'test_question_id', 'test_answer_option_id',
        'score', 'time_spent_seconds', 'answered_at',
    ];

    protected $casts = [
        'score'              => 'integer',
        'time_spent_seconds' => 'integer',
        'answered_at'        => 'datetime',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(TestAttempt::class, 'test_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(TestQuestion::class, 'test_question_id');
    }

    public function answerOption(): BelongsTo
    {
        return $this->belongsTo(TestAnswerOption::class, 'test_answer_option_id');
    }
}
