<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CommissionSide;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class TradeCommission extends Model
{
    protected $fillable = [
        'trade_id',
        'trade_exit_id',
        'commission_rate_id',
        'side',
        'quantity',
        'commission_rate',
        'commission_amount',
        'vat_rate',
        'vat_amount',
        'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'side' => CommissionSide::class,
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }

    public function tradeExit(): BelongsTo
    {
        return $this->belongsTo(TradeExit::class);
    }

    public function commissionRate(): BelongsTo
    {
        return $this->belongsTo(CommissionRate::class);
    }
}
