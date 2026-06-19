<?php

namespace App\Models;

use App\Enums\PositionType;
use App\Enums\TradeStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trade extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'trading_account_id',
        'contract_id',
        'trade_date',
        'position_type',
        'quantity',
        'closed_quantity',
        'remaining_quantity',
        'entry_price',
        'entry_date',
        'entry_time',
        'status',
        'total_gross_profit',
        'total_commission',
        'total_vat',
        'total_net_profit',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'trade_date' => 'date',
            'entry_date' => 'date',
            'position_type' => PositionType::class,
            'status' => TradeStatus::class,
            'entry_price' => 'decimal:2',
            'total_gross_profit' => 'decimal:2',
            'total_commission' => 'decimal:2',
            'total_vat' => 'decimal:2',
            'total_net_profit' => 'decimal:2',
        ];
    }

    public function tradingAccount(): BelongsTo
    {
        return $this->belongsTo(TradingAccount::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function exits(): HasMany
    {
        return $this->hasMany(TradeExit::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(TradeCommission::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(TradingNote::class);
    }

    public function screenshots(): HasMany
    {
        return $this->hasMany(TradingScreenshot::class);
    }

    public function isOpen(): bool
    {
        return $this->status === TradeStatus::Open;
    }

    public function isPartialClosed(): bool
    {
        return $this->status === TradeStatus::PartialClosed;
    }

    public function isClosed(): bool
    {
        return $this->status === TradeStatus::Closed;
    }

    public function canClose(): bool
    {
        return $this->remaining_quantity > 0 && ! $this->isClosed();
    }
}
