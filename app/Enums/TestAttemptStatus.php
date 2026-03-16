<?php

namespace App\Enums;

enum TestAttemptStatus: string
{
    case InProgress = 'in_progress';
    case Completed  = 'completed';
    case Abandoned  = 'abandoned';

    public function label(): string
    {
        return match ($this) {
            self::InProgress => 'In Progress',
            self::Completed  => 'Completed',
            self::Abandoned  => 'Abandoned',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::InProgress => 'warning',
            self::Completed  => 'success',
            self::Abandoned  => 'gray',
        };
    }
}
