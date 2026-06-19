<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\CommissionSide;

final readonly class CommissionData
{
    public function __construct(
        public ?int $commissionRateId,
        public CommissionSide|string $side,
        public int $quantity,
        public float $commissionRate,
        public float $commissionAmount,
        public float $vatRate,
        public float $vatAmount,
        public float $totalAmount,
    ) {}

    public function sideValue(): string
    {
        if ($this->side instanceof CommissionSide) {
            return $this->side->value;
        }

        return $this->side;
    }

    public function toArray(): array
    {
        return [
            'commission_rate_id' => $this->commissionRateId,
            'side' => $this->sideValue(),
            'quantity' => $this->quantity,
            'commission_rate' => round($this->commissionRate, 2),
            'commission_amount' => round($this->commissionAmount, 2),
            'vat_rate' => round($this->vatRate, 2),
            'vat_amount' => round($this->vatAmount, 2),
            'total_amount' => round($this->totalAmount, 2),
        ];
    }
}
