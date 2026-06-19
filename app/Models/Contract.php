<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'symbol',
        'name',
        'underlying',
        'multiplier',
        'tick_size',
        'tick_value',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'multiplier' => 'decimal:2',
            'tick_size' => 'decimal:2',
            'tick_value' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }
}
