<?php
declare(strict_types=1);
namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use App\Models\Contract;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ContractForm extends Form
{
    public ?int $id = null;

    public ?string $symbol = null;

    public ?string $name = null;

    public ?string $underlying = null;

    public float|string|null $multiplier = 200;

    public float|string|null $tick_size = 0.10;

    public float|string|null $tick_value = 20;

    public bool $is_active = true;

    public function rules(): array
    {
        return [
            'symbol' => [
                'required',
                'string',
                'max:50',
                Rule::unique('contracts', 'symbol')->ignore($this->id),
            ],
            'name' => [
                'nullable',
                'string',
                'max:150',
            ],
            'underlying' => [
                'nullable',
                'string',
                'max:100',
            ],
            'multiplier' => [
                'required',
                'numeric',
                'gt:0',
            ],
            'tick_size' => [
                'required',
                'numeric',
                'gt:0',
            ],
            'tick_value' => [
                'required',
                'numeric',
                'gt:0',
            ],
            'is_active' => [
                'boolean',
            ],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'symbol' => 'Contract Symbol',
            'name' => 'ชื่อ Contract',
            'underlying' => 'Underlying',
            'multiplier' => 'Multiplier',
            'tick_size' => 'Tick Size',
            'tick_value' => 'Tick Value',
            'is_active' => 'สถานะใช้งาน',
        ];
    }

    public function setContract(Contract $contract): void
    {
        $this->id = $contract->id;
        $this->symbol = $contract->symbol;
        $this->name = $contract->name;
        $this->underlying = $contract->underlying;
        $this->multiplier = $contract->multiplier;
        $this->tick_size = $contract->tick_size;
        $this->tick_value = $contract->tick_value;
        $this->is_active = (bool) $contract->is_active;
    }

    public function toArray(): array
    {
        $this->validate();

        return [
            'symbol' => strtoupper((string) $this->symbol),
            'name' => $this->name,
            'underlying' => $this->underlying,
            'multiplier' => round((float) $this->multiplier, 2),
            'tick_size' => round((float) $this->tick_size, 2),
            'tick_value' => round((float) $this->tick_value, 2),
            'is_active' => $this->is_active,
        ];
    }

    public function resetForm(): void
    {
        $this->reset();

        $this->multiplier = 200;
        $this->tick_size = 0.10;
        $this->tick_value = 20;
        $this->is_active = true;
    }
}
