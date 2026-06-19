<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TradingAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'initial_balance',
        'current_balance',
        'started_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'initial_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'started_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }

    public function equitySnapshots(): HasMany
    {
        return $this->hasMany(EquitySnapshot::class);
    }
}
