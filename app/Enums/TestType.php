<?php

namespace App\Enums;

enum TestType: string
{
    case YesNo  = 'yes_no';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::YesNo  => 'Yes / No',
            self::Custom => 'Custom Options',
        };
    }
}
