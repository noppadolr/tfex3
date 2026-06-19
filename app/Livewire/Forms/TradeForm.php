<?php
declare(strict_types=1);

namespace App\Livewire\Forms;

use App\DTOs\TradeData;
use App\Enums\PositionType;
use App\Models\Trade;
use Illuminate\Validation\Rule;
use Livewire\Form;
//namespace App\Livewire\Forms;
//
//use Livewire\Attributes\Validate;
//use Livewire\Form;

class TradeForm extends Form
{
    public ?int $id = null;

    public ?int $trading_account_id = null;

    public ?int $contract_id = null;

    public ?string $trade_date = null;

    public string $position_type = 'long';

    public int|string|null $quantity = null;

    public float|string|null $entry_price = null;

    public ?string $entry_date = null;

    public ?string $entry_time = null;

    public ?string $note = null;

    public function rules(): array
    {
        return [
            'trading_account_id' => [
                'required',
                'integer',
                'exists:trading_accounts,id',
            ],
            'contract_id' => [
                'required',
                'integer',
                'exists:contracts,id',
            ],
            'trade_date' => [
                'required',
                'date',
            ],
            'position_type' => [
                'required',
                Rule::in([
                    PositionType::Long->value,
                    PositionType::Short->value,
                ]),
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
            'entry_price' => [
                'required',
                'numeric',
                'gt:0',
            ],
            'entry_date' => [
                'required',
                'date',
            ],
            'entry_time' => [
                'nullable',
                'date_format:H:i',
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
            'trading_account_id' => 'บัญชีเทรด',
            'contract_id' => 'Contract',
            'trade_date' => 'วันที่เทรด',
            'position_type' => 'ประเภทสถานะ Long / Short',
            'quantity' => 'จำนวนสัญญา',
            'entry_price' => 'ราคาเข้า',
            'entry_date' => 'วันที่เข้า',
            'entry_time' => 'เวลาเข้า',
            'note' => 'หมายเหตุ',
        ];
    }

    public function setTrade(Trade $trade): void
    {
        $this->id = $trade->id;
        $this->trading_account_id = $trade->trading_account_id;
        $this->contract_id = $trade->contract_id;
        $this->trade_date = $trade->trade_date?->format('Y-m-d');
        $this->position_type = $trade->position_type->value;
        $this->quantity = $trade->quantity;
        $this->entry_price = $trade->entry_price;
        $this->entry_date = $trade->entry_date?->format('Y-m-d');
        $this->entry_time = $trade->entry_time
            ? substr((string) $trade->entry_time, 0, 5)
            : null;
        $this->note = $trade->note;
    }

    public function toData(): TradeData
    {
        $this->validate();

        return TradeData::fromArray([
            'trading_account_id' => $this->trading_account_id,
            'contract_id' => $this->contract_id,
            'trade_date' => $this->trade_date,
            'position_type' => $this->position_type,
            'quantity' => $this->quantity,
            'entry_price' => $this->entry_price,
            'entry_date' => $this->entry_date,
            'entry_time' => $this->normalizeTime($this->entry_time),
            'note' => $this->note,
        ]);
    }

    public function resetForm(): void
    {
        $this->reset();

        $this->position_type = PositionType::Long->value;
        $this->trade_date = now()->toDateString();
        $this->entry_date = now()->toDateString();
    }

    private function normalizeTime(?string $time): ?string
    {
        if (! $time) {
            return null;
        }

        if (strlen($time) === 5) {
            return $time . ':00';
        }

        return $time;
    }
}
