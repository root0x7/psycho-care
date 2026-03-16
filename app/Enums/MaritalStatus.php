<?php

namespace App\Enums;

enum MaritalStatus: string
{
    case Single   = 'single';
    case Married  = 'married';
    case Divorced = 'divorced';

    public function label(): string
    {
        return match ($this) {
            self::Single   => 'Single',
            self::Married  => 'Married',
            self::Divorced => 'Divorced',
        };
    }
}
