<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EquitySnapshot;
use App\Models\TradeExit;
use App\Models\TradingAccount;
use App\Repositories\EquitySnapshotRepository;

class EquitySnapshotService
{
    public function __construct(
        private readonly EquitySnapshotRepository $equitySnapshotRepository,
    ) {}

    public function createAfterTradeExit(
        TradingAccount $account,
        string $snapshotDate,
        float $netProfit,
    ): EquitySnapshot {
        $account = $account->fresh();

        $balance = (float) $account->current_balance;
        $equity = $balance;

        $previousPeak = $this->getPreviousPeakEquity($account, $balance);

        $drawdown = max(0, $previousPeak - $equity);
        $drawdownPercent = $previousPeak > 0
            ? ($drawdown / $previousPeak) * 100
            : 0;

        $dailyNetProfit = $this->getDailyNetProfit(
            account: $account,
            snapshotDate: $snapshotDate,
        );

        return $this->equitySnapshotRepository->updateOrCreate(
            account: $account,
            snapshotDate: $snapshotDate,
            balance: $balance,
            equity: $equity,
            netProfit: $dailyNetProfit,
            drawdown: $drawdown,
            drawdownPercent: $drawdownPercent,
        );
    }

    private function getPreviousPeakEquity(
        TradingAccount $account,
        float $currentBalance,
    ): float {
        $highestEquity = $this->equitySnapshotRepository->highestEquity($account);

        return max($highestEquity, (float) $account->initial_balance, $currentBalance);
    }

    private function getDailyNetProfit(
        TradingAccount $account,
        string $snapshotDate,
    ): float {
        return (float) TradeExit::query()
            ->whereDate('exit_date', $snapshotDate)
            ->whereHas('trade', function ($query) use ($account): void {
                $query->where('trading_account_id', $account->id);
            })
            ->sum('net_profit');
    }
}
