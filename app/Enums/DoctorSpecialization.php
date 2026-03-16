<?php

namespace App\Enums;

enum DoctorSpecialization: string
{
    case Psychologist = 'psychologist';
    case Psychiatrist = 'psychiatrist';

    public function label(): string
    {
        return match ($this) {
            self::Psychologist => 'Psychologist',
            self::Psychiatrist => 'Psychiatrist',
        };
    }
}
