<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class ChartPointData
{
    public function __construct(
        public string $label,
        public int|float $value,
    ) {}

    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'value' => $this->value,
        ];
    }
}
