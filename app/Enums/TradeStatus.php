<?php

declare(strict_types=1);

namespace App\Enums;

enum TradeStatus: string
{
    case Open = 'open';
    case PartialClosed = 'partial_closed';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::PartialClosed => 'Partial Closed',
            self::Closed => 'Closed',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Open => 'blue',
            self::PartialClosed => 'amber',
            self::Closed => 'green',
        };
    }
}
