<?php

namespace App\Enums;

enum MoodChannel: string
{
    case Web      = 'web';
    case Telegram = 'telegram';

    public function label(): string
    {
        return match ($this) {
            self::Web      => 'Web',
            self::Telegram => 'Telegram',
        };
    }
}
