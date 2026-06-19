<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class ChartDatasetData
{
    /**
     * @param array<int, ChartPointData> $points
     */
    public function __construct(
        public string $name,
        public array $points,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'points' => array_map(
                static fn (ChartPointData $point): array => $point->toArray(),
                $this->points
            ),
        ];
    }
}
