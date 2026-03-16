<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestQuestion extends Model
{
    protected $fillable = ['test_section_id', 'text', 'sort_order', 'is_required'];

    protected $casts = [
        'sort_order'  => 'integer',
        'is_required' => 'boolean',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(TestSection::class, 'test_section_id');
    }

    public function answerOptions(): HasMany
    {
        return $this->hasMany(TestAnswerOption::class)->orderBy('sort_order');
    }
}
