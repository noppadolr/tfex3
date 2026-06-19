<?php

declare(strict_types=1);

use Carbon\Carbon;

if (! function_exists('thai_date')) {
    function thai_date(null|string|DateTimeInterface $date): string
    {
        if (! $date) {
            return '-';
        }

        $carbon = Carbon::parse($date);

        return $carbon->format('d/m/').($carbon->year + 543);
    }
}

if (! function_exists('thai_datetime')) {
    function thai_datetime(null|string|DateTimeInterface $date): string
    {
        if (! $date) {
            return '-';
        }

        $carbon = Carbon::parse($date);

        return $carbon->format('d/m/').($carbon->year + 543).' '.$carbon->format('H:i');
    }
}
