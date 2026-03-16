<?php

namespace App\Models;

use App\Enums\RiskLevel;
use App\Enums\RiskKeywordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiskKeyword extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'phrase', 'locale', 'severity', 'status',
        'is_phrase_match', 'created_by', 'reviewed_by',
        'reviewed_at', 'rejection_reason',
    ];

    protected $casts = [
        'severity'        => RiskLevel::class,
        'status'          => RiskKeywordStatus::class,
        'is_phrase_match' => 'boolean',
        'reviewed_at'     => 'datetime',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(RiskKeywordMatch::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', RiskKeywordStatus::Approved->value);
    }
}
