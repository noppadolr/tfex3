<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTOs\CommissionData;
use App\Enums\CommissionSide;
use App\Models\Trade;
use App\Models\TradeCommission;
use App\Models\TradeExit;
use Illuminate\Database\Eloquent\Collection;

class TradeCommissionRepository
{
    public function createForTrade(
        Trade $trade,
        CommissionData $data
    ): TradeCommission {
        return TradeCommission::query()->create([
            'trade_id' => $trade->id,
            'trade_exit_id' => null,
            ...$data->toArray(),
        ]);
    }

    public function createForTradeExit(
        Trade $trade,
        TradeExit $tradeExit,
        CommissionData $data
    ): TradeCommission {
        return TradeCommission::query()->create([
            'trade_id' => $trade->id,
            'trade_exit_id' => $tradeExit->id,
            ...$data->toArray(),
        ]);
    }

    public function entryCommissionTotal(Trade $trade): float
    {
        return (float) TradeCommission::query()
            ->where('trade_id', $trade->id)
            ->where('side', CommissionSide::Entry->value)
            ->sum('commission_amount');
    }

    public function entryVatTotal(Trade $trade): float
    {
        return (float) TradeCommission::query()
            ->where('trade_id', $trade->id)
            ->where('side', CommissionSide::Entry->value)
            ->sum('vat_amount');
    }

    public function exitCommissionTotal(Trade $trade): float
    {
        return (float) TradeCommission::query()
            ->where('trade_id', $trade->id)
            ->where('side', CommissionSide::Exit->value)
            ->sum('commission_amount');
    }

    public function exitVatTotal(Trade $trade): float
    {
        return (float) TradeCommission::query()
            ->where('trade_id', $trade->id)
            ->where('side', CommissionSide::Exit->value)
            ->sum('vat_amount');
    }

    public function totalCommission(): float
    {
        return (float) TradeCommission::query()->sum('commission_amount');
    }

    public function totalVat(): float
    {
        return (float) TradeCommission::query()->sum('vat_amount');
    }

    public function totalCost(): float
    {
        return (float) TradeCommission::query()->sum('total_amount');
    }

    public function getByTrade(Trade $trade): Collection
    {
        return TradeCommission::query()
            ->where('trade_id', $trade->id)
            ->orderBy('created_at')
            ->get();
    }
}
