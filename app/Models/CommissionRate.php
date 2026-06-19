<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommissionRate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'commission_per_contract',
        'vat_rate',
        'effective_from',
        'effective_to',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'commission_per_contract' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'effective_from' => 'date',
            'effective_to' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function tradeCommissions(): HasMany
    {
        return $this->hasMany(TradeCommission::class);
    }
}
