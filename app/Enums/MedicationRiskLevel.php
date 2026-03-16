<?php

namespace App\Enums;

enum MedicationRiskLevel: string
{
    case Compatible         = 'compatible';
    case LikelyCompatible   = 'likely_compatible';
    case LimitedCompatible  = 'limited_compatible';
    case Incompatible       = 'incompatible';

    public function label(): string
    {
        return match ($this) {
            self::Compatible        => 'Compatible (Safe, best option.)',
            self::LikelyCompatible  => 'Likely Compatible (Fairly safe.)',
            self::LimitedCompatible => 'Limited Compatibility (Unsafe.)',
            self::Incompatible      => 'Incompatible (Very unsafe.)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Compatible        => 'success',
            self::LikelyCompatible  => 'info',
            self::LimitedCompatible => 'warning',
            self::Incompatible      => 'danger',
        };
    }
}
