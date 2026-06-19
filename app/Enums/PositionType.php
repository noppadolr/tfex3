<?php

declare(strict_types=1);

namespace App\Enums;

enum PositionType: string
{
    case Long = 'long';
    case Short = 'short';

    public function label(): string
    {
        return match ($this) {
            self::Long => 'Long',
            self::Short => 'Short',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Long => 'green',
            self::Short => 'red',
        };
    }
}
