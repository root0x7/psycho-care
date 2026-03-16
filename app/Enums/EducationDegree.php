<?php

namespace App\Enums;

enum EducationDegree: string
{
    case Bachelor  = 'bachelor';
    case Master    = 'master';
    case Residency = 'residency';

    public function label(): string
    {
        return match ($this) {
            self::Bachelor  => 'Bachelor',
            self::Master    => 'Master',
            self::Residency => 'Residency',
        };
    }
}
