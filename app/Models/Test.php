<?php

namespace App\Models;

use App\Enums\TestType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Test extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'test_category_id', 'name', 'slug', 'description',
        'type', 'version', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'type'      => TestType::class,
        'is_active' => 'boolean',
        'version'   => 'integer',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(TestCategory::class, 'test_category_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(TestSection::class)->orderBy('sort_order');
    }

    public function interpretationRules(): HasMany
    {
        return $this->hasMany(TestInterpretationRule::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class);
    }

    public function questions(): HasManyThrough
    {
        return $this->hasManyThrough(TestQuestion::class, TestSection::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
