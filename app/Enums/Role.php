<?php

namespace App\Enums;

enum Role: string
{
    case Superadmin = 'superadmin';
    case Doctor     = 'doctor';
    case Patient    = 'patient';

    public function label(): string
    {
        return match ($this) {
            self::Superadmin => 'Super Admin',
            self::Doctor     => 'Doctor',
            self::Patient    => 'Patient',
        };
    }
}
