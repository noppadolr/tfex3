<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\EquitySnapshot;
use App\Models\TradingAccount;
use Illuminate\Database\Eloquent\Collection;

class EquitySnapshotRepository
{
    public function updateOrCreate(
        TradingAccount $account,
        string $snapshotDate,
        float $balance,
        float $equity,
        float $netProfit,
        float $drawdown,
        float $drawdownPercent
    ): EquitySnapshot {
        return EquitySnapshot::query()->updateOrCreate(
            [
                'trading_account_id' => $account->id,
                'snapshot_date' => $snapshotDate,
            ],
            [
                'balance' => round($balance, 2),
                'equity' => round($equity, 2),
                'net_profit' => round($netProfit, 2),
                'drawdown' => round($drawdown, 2),
                'drawdown_percent' => round($drawdownPercent, 2),
            ]
        );
    }

    public function getByAccount(TradingAccount $account): Collection
    {
        return EquitySnapshot::query()
            ->where('trading_account_id', $account->id)
            ->orderBy('snapshot_date')
            ->get();
    }

    public function lowestEquity(TradingAccount $account): float
    {
        return (float) EquitySnapshot::query()
            ->where('trading_account_id', $account->id)
            ->min('equity');
    }

    public function highestEquity(TradingAccount $account): float
    {
        return (float) EquitySnapshot::query()
            ->where('trading_account_id', $account->id)
            ->max('equity');
    }

    public function maxDrawdown(TradingAccount $account): float
    {
        return (float) EquitySnapshot::query()
            ->where('trading_account_id', $account->id)
            ->max('drawdown');
    }

    public function maxDrawdownPercent(TradingAccount $account): float
    {
        return (float) EquitySnapshot::query()
            ->where('trading_account_id', $account->id)
            ->max('drawdown_percent');
    }

    public function latest(TradingAccount $account): ?EquitySnapshot
    {
        return EquitySnapshot::query()
            ->where('trading_account_id', $account->id)
            ->latest('snapshot_date')
            ->first();
    }
}
