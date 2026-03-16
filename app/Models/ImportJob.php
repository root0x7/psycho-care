<?php

namespace App\Models;

use App\Enums\ImportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportJob extends Model
{
    protected $fillable = [
        'source', 'type', 'status',
        'records_processed', 'records_imported', 'records_failed',
        'error_message', 'triggered_by',
        'started_at', 'completed_at',
    ];

    protected $casts = [
        'status'            => ImportStatus::class,
        'started_at'        => 'datetime',
        'completed_at'      => 'datetime',
        'records_processed' => 'integer',
        'records_imported'  => 'integer',
        'records_failed'    => 'integer',
    ];

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(ImportSourceSnapshot::class);
    }
}
