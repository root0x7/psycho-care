<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestSection extends Model
{
    protected $fillable = ['test_id', 'name', 'description', 'sort_order', 'max_score'];

    protected $casts = [
        'sort_order' => 'integer',
        'max_score'  => 'integer',
    ];

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(TestQuestion::class)->orderBy('sort_order');
    }

    public function interpretationRules(): HasMany
    {
        return $this->hasMany(TestInterpretationRule::class);
    }
}
