<?php

namespace App\Enums;

enum RiskLevel: string
{
    case Low      = 'low';
    case Medium   = 'medium';
    case Critical = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::Low      => 'Low',
            self::Medium   => 'Medium',
            self::Critical => 'Critical',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Low      => 'success',
            self::Medium   => 'warning',
            self::Critical => 'danger',
        };
    }

    public function hexColor(): string
    {
        return match ($this) {
            self::Low      => '#22c55e',
            self::Medium   => '#eab308',
            self::Critical => '#ef4444',
        };
    }
}
