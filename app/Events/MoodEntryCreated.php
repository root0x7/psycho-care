<?php

namespace App\Events;

use App\Models\MoodEntry;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MoodEntryCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly MoodEntry $moodEntry) {}
}
