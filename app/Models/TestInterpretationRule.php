<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestInterpretationRule extends Model
{
    protected $fillable = [
        'test_id', 'test_section_id', 'score_from', 'score_to',
        'label', 'description', 'severity',
    ];

    protected $casts = [
        'score_from' => 'integer',
        'score_to'   => 'integer',
    ];

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(TestSection::class, 'test_section_id');
    }
}
