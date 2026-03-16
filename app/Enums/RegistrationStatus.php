<?php

namespace App\Enums;

enum RegistrationStatus: string
{
    case Pending  = 'pending';
    case Complete = 'complete';

    public function label(): string
    {
        return match ($this) {
            self::Pending  => 'Pending',
            self::Complete => 'Complete',
        };
    }
}
