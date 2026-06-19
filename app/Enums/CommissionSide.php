<?php

declare(strict_types=1);

namespace App\Enums;

enum CommissionSide: string
{
    case Entry = 'entry';
    case Exit = 'exit';

    public function label(): string
    {
        return match ($this) {
            self::Entry => 'Entry',
            self::Exit => 'Exit',
        };
    }
}
