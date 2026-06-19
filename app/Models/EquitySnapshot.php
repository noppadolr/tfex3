<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquitySnapshot extends Model
{
    protected $fillable = [
        'trading_account_id',
        'snapshot_date',
        'balance',
        'equity',
        'net_profit',
        'drawdown',
        'drawdown_percent',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_date' => 'date',
            'balance' => 'decimal:2',
            'equity' => 'decimal:2',
            'net_profit' => 'decimal:2',
            'drawdown' => 'decimal:2',
            'drawdown_percent' => 'decimal:2',
        ];
    }

    public function tradingAccount(): BelongsTo
    {
        return $this->belongsTo(TradingAccount::class);
    }
}
