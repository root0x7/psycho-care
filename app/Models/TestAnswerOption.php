<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestAnswerOption extends Model
{
    protected $fillable = ['test_question_id', 'text', 'score', 'sort_order'];

    protected $casts = [
        'score'      => 'integer',
        'sort_order' => 'integer',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(TestQuestion::class, 'test_question_id');
    }
}
