<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\DTOs\TradeExitData;
use App\Models\Trade;
use App\Models\TradeExit;
use Livewire\Form;

class TradeExitForm extends Form
{
    public ?int $id = null;

    public ?int $trade_id = null;

    public ?string $exit_date = null;

    public ?string $exit_time = null;

    public int|string|null $close_quantity = null;

    public float|string|null $exit_price = null;

    public ?string $note = null;

    public ?int $remaining_quantity = null;

    public function rules(): array
    {
        return [
            'trade_id' => [
                'required',
                'integer',
                'exists:trades,id',
            ],
            'exit_date' => [
                'required',
                'date',
            ],
            'exit_time' => [
                'nullable',
                'date_format:H:i',
            ],
            'close_quantity' => [
                'required',
                'integer',
                'min:1',
                'max:'.max(1, (int) $this->remaining_quantity),
            ],
            'exit_price' => [
                'required',
                'numeric',
                'gt:0',
            ],
            'note' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'trade_id' => 'รายการเทรด',
            'exit_date' => 'วันที่ออก',
            'exit_time' => 'เวลาออก',
            'close_quantity' => 'จำนวนสัญญาที่ปิด',
            'exit_price' => 'ราคาออก',
            'note' => 'หมายเหตุ',
        ];
    }

    public function setTrade(Trade $trade): void
    {
        $this->trade_id = $trade->id;
        $this->remaining_quantity = (int) $trade->remaining_quantity;
        $this->close_quantity = (int) $trade->remaining_quantity;
        $this->exit_date = now()->toDateString();
        $this->exit_time = now()->format('H:i');
        $this->exit_price = null;
        $this->note = null;
    }

    public function setTradeExit(TradeExit $tradeExit): void
    {
        $this->id = $tradeExit->id;
        $this->trade_id = $tradeExit->trade_id;
        $this->exit_date = $tradeExit->exit_date?->format('Y-m-d');
        $this->exit_time = $tradeExit->exit_time
            ? substr((string) $tradeExit->exit_time, 0, 5)
            : null;
        $this->close_quantity = $tradeExit->close_quantity;
        $this->exit_price = $tradeExit->exit_price;
        $this->note = $tradeExit->note;
        $this->remaining_quantity = $tradeExit->trade?->remaining_quantity;
    }

    public function toData(): TradeExitData
    {
        $this->validate();

        return TradeExitData::fromArray([
            'trade_id' => $this->trade_id,
            'exit_date' => $this->exit_date,
            'exit_time' => $this->normalizeTime($this->exit_time),
            'close_quantity' => $this->close_quantity,
            'exit_price' => $this->exit_price,
            'note' => $this->note,
        ]);
    }

    public function resetForm(): void
    {
        $this->reset();

        $this->exit_date = now()->toDateString();
        $this->exit_time = now()->format('H:i');
    }

    private function normalizeTime(?string $time): ?string
    {
        if (! $time) {
            return null;
        }

        if (strlen($time) === 5) {
            return $time.':00';
        }

        return $time;
    }
}
