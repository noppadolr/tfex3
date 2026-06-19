<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TradeExit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'trade_id',
        'exit_date',
        'exit_time',
        'close_quantity',
        'exit_price',
        'gross_profit',
        'entry_commission_allocated',
        'entry_vat_allocated',
        'exit_commission_amount',
        'exit_vat_amount',
        'total_cost',
        'net_profit',
        'balance_after_close',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'exit_date' => 'date',
            'exit_price' => 'decimal:2',
            'gross_profit' => 'decimal:2',
            'entry_commission_allocated' => 'decimal:2',
            'entry_vat_allocated' => 'decimal:2',
            'exit_commission_amount' => 'decimal:2',
            'exit_vat_amount' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'net_profit' => 'decimal:2',
            'balance_after_close' => 'decimal:2',
        ];
    }

    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(TradeCommission::class);
    }
}
