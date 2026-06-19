<?php

declare(strict_types=1);

namespace App\DTOs;

use Carbon\CarbonInterface;

final readonly class TradeExitData
{
    public function __construct(
        public int $tradeId,
        public CarbonInterface|string $exitDate,
        public ?string $exitTime,
        public int $closeQuantity,
        public float $exitPrice,
        public ?string $note = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            tradeId: (int) $data['trade_id'],
            exitDate: $data['exit_date'],
            exitTime: $data['exit_time'] ?? null,
            closeQuantity: (int) $data['close_quantity'],
            exitPrice: (float) $data['exit_price'],
            note: $data['note'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'trade_id' => $this->tradeId,
            'exit_date' => $this->exitDate,
            'exit_time' => $this->exitTime,
            'close_quantity' => $this->closeQuantity,
            'exit_price' => $this->exitPrice,
            'note' => $this->note,
        ];
    }
}
