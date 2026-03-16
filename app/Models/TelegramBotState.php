<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramBotState extends Model
{
    protected $fillable = [
        'telegram_id', 'user_id', 'current_state', 'context', 'last_interaction_at',
    ];

    protected $casts = [
        'context'              => 'array',
        'last_interaction_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
