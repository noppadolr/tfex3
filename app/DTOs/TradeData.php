<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\PositionType;
use Carbon\CarbonInterface;

final readonly class TradeData
{
    public function __construct(
        public int $tradingAccountId,
        public int $contractId,
        public CarbonInterface|string $tradeDate,
        public PositionType|string $positionType,
        public int $quantity,
        public float $entryPrice,
        public CarbonInterface|string $entryDate,
        public ?string $entryTime = null,
        public ?string $note = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            tradingAccountId: (int) $data['trading_account_id'],
            contractId: (int) $data['contract_id'],
            tradeDate: $data['trade_date'],
            positionType: $data['position_type'],
            quantity: (int) $data['quantity'],
            entryPrice: (float) $data['entry_price'],
            entryDate: $data['entry_date'],
            entryTime: $data['entry_time'] ?? null,
            note: $data['note'] ?? null,
        );
    }

    public function positionTypeValue(): string
    {
        if ($this->positionType instanceof PositionType) {
            return $this->positionType->value;
        }

        return $this->positionType;
    }

    public function toArray(): array
    {
        return [
            'trading_account_id' => $this->tradingAccountId,
            'contract_id' => $this->contractId,
            'trade_date' => $this->tradeDate,
            'position_type' => $this->positionTypeValue(),
            'quantity' => $this->quantity,
            'closed_quantity' => 0,
            'remaining_quantity' => $this->quantity,
            'entry_price' => $this->entryPrice,
            'entry_date' => $this->entryDate,
            'entry_time' => $this->entryTime,
            'note' => $this->note,
        ];
    }
}
