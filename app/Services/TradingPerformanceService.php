<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ChartDatasetData;
use App\DTOs\ChartPointData;
use App\DTOs\PerformanceSummaryData;
use App\Enums\PositionType;
use App\Enums\TradeStatus;
use App\Models\EquitySnapshot;
use App\Models\Trade;
use App\Models\TradeExit;
use App\Models\TradingAccount;
use App\Repositories\TradingAccountRepository;
use Illuminate\Support\Collection;

class TradingPerformanceService
{
    public function __construct(
        private readonly TradingAccountRepository $tradingAccountRepository,
    ) {}

    public function summary(?int $tradingAccountId = null): PerformanceSummaryData
    {
        $account = $this->resolveAccount($tradingAccountId);

        if (! $account) {
            return PerformanceSummaryData::empty();
        }

        $tradeExits = $this->closedResults($account);
        $snapshots = $this->equitySnapshots($account);

        if ($tradeExits->isEmpty()) {
            return PerformanceSummaryData::empty(
                initialBalance: (float) $account->initial_balance,
            );
        }

        $initialBalance = (float) $account->initial_balance;
        $currentBalance = (float) $account->current_balance;

        $totalNetProfit = (float) $tradeExits->sum('net_profit');

        $totalNetProfitPercent = $initialBalance > 0
            ? ($totalNetProfit / $initialBalance) * 100
            : 0;

        $grossProfit = (float) $tradeExits
            ->where('net_profit', '>', 0)
            ->sum('net_profit');

        $grossLoss = (float) $tradeExits
            ->where('net_profit', '<', 0)
            ->sum('net_profit');

        $totalTrade = Trade::query()
            ->where('trading_account_id', $account->id)
            ->where('status', TradeStatus::Closed->value)
            ->count();

        $totalTradeExit = $tradeExits->count();

        $expectedPayoff = $totalTradeExit > 0
            ? $totalNetProfit / $totalTradeExit
            : 0;

        $lowestEquity = $snapshots->min('equity') ?? $currentBalance;

        $absoluteDrawdown = max(0, $initialBalance - (float) $lowestEquity);

        $maximalDrawdown = $this->calculateMaximalDrawdown($snapshots);

        $longStats = $this->positionStats(
            tradeExits: $tradeExits,
            positionType: PositionType::Long,
        );

        $shortStats = $this->positionStats(
            tradeExits: $tradeExits,
            positionType: PositionType::Short,
        );

        $largestProfitTrade = (float) $tradeExits
            ->where('net_profit', '>', 0)
            ->max('net_profit');

        $averageProfitTrade = (float) $tradeExits
            ->where('net_profit', '>', 0)
            ->avg('net_profit');

        $maxConsecutiveProfit = $this->maxConsecutive(
            values: $tradeExits->pluck('net_profit')->map(fn ($value): float => (float) $value),
            type: 'profit',
        );

        $maxConsecutiveLoss = $this->maxConsecutive(
            values: $tradeExits->pluck('net_profit')->map(fn ($value): float => (float) $value),
            type: 'loss',
        );

        $totalCommission = (float) $tradeExits->sum('exit_commission_amount')
            + (float) $tradeExits->sum('entry_commission_allocated');

        $totalVat = (float) $tradeExits->sum('exit_vat_amount')
            + (float) $tradeExits->sum('entry_vat_allocated');

        $totalCost = (float) $tradeExits->sum('total_cost');

        return new PerformanceSummaryData(
            initialBalance: round($initialBalance, 2),
            currentBalance: round($currentBalance, 2),
            totalNetProfit: round($totalNetProfit, 2),
            totalNetProfitPercent: round($totalNetProfitPercent, 2),
            grossProfit: round($grossProfit, 2),
            grossLoss: round($grossLoss, 2),
            absoluteDrawdown: round($absoluteDrawdown, 2),
            maximalDrawdown: round($maximalDrawdown, 2),
            expectedPayoff: round($expectedPayoff, 2),
            totalTrade: $totalTrade,
            totalTradeExit: $totalTradeExit,
            longWinRate: round($longStats['win_rate'], 2),
            profitTradesOfTotalLong: round($longStats['profit_rate'], 2),
            shortWinRate: round($shortStats['win_rate'], 2),
            lossTradesOfTotalShort: round($shortStats['loss_rate'], 2),
            largestProfitTrade: round($largestProfitTrade, 2),
            averageProfitTrade: round($averageProfitTrade, 2),
            maximalConsecutiveProfitTimes: $maxConsecutiveProfit,
            maximalConsecutiveLossTimes: $maxConsecutiveLoss,
            totalCommission: round($totalCommission, 2),
            totalVat: round($totalVat, 2),
            totalCost: round($totalCost, 2),
        );
    }

    public function positionSizeChart(?int $tradingAccountId = null): ChartDatasetData
    {
        $account = $this->resolveAccount($tradingAccountId);

        if (! $account) {
            return new ChartDatasetData(
                name: 'Position Size',
                points: [],
            );
        }

        $trades = Trade::query()
            ->where('trading_account_id', $account->id)
            ->orderBy('trade_date')
            ->orderBy('id')
            ->get();

        $points = $trades->map(function (Trade $trade): ChartPointData {
            return new ChartPointData(
                label: thai_date($trade->trade_date),
                value: (int) $trade->quantity,
            );
        })->values()->all();

        return new ChartDatasetData(
            name: 'Position Size',
            points: $points,
        );
    }

    public function equityCurveChart(?int $tradingAccountId = null): ChartDatasetData
    {
        $account = $this->resolveAccount($tradingAccountId);

        if (! $account) {
            return new ChartDatasetData(
                name: 'Equity Curve',
                points: [],
            );
        }

        $snapshots = $this->equitySnapshots($account);

        $points = $snapshots->map(function (EquitySnapshot $snapshot): ChartPointData {
            return new ChartPointData(
                label: thai_date($snapshot->snapshot_date),
                value: (float) $snapshot->equity,
            );
        })->values()->all();

        return new ChartDatasetData(
            name: 'Equity Curve',
            points: $points,
        );
    }

    private function resolveAccount(?int $tradingAccountId = null): ?TradingAccount
    {
        if ($tradingAccountId) {
            return $this->tradingAccountRepository->findOrFail($tradingAccountId);
        }

        return $this->tradingAccountRepository->defaultAccount();
    }

    private function closedResults(TradingAccount $account): Collection
    {
        return TradeExit::query()
            ->with(['trade.contract'])
            ->whereHas('trade', function ($query) use ($account): void {
                $query->where('trading_account_id', $account->id);
            })
            ->orderBy('exit_date')
            ->orderBy('exit_time')
            ->orderBy('id')
            ->get();
    }

    private function equitySnapshots(TradingAccount $account): Collection
    {
        return EquitySnapshot::query()
            ->where('trading_account_id', $account->id)
            ->orderBy('snapshot_date')
            ->orderBy('id')
            ->get();
    }

    private function calculateMaximalDrawdown(Collection $snapshots): float
    {
        if ($snapshots->isEmpty()) {
            return 0;
        }

        $peak = 0;
        $maxDrawdown = 0;

        foreach ($snapshots as $snapshot) {
            $equity = (float) $snapshot->equity;

            if ($equity > $peak) {
                $peak = $equity;
            }

            $drawdown = $peak - $equity;

            if ($drawdown > $maxDrawdown) {
                $maxDrawdown = $drawdown;
            }
        }

        return $maxDrawdown;
    }

    private function positionStats(
        Collection $tradeExits,
        PositionType $positionType,
    ): array {
        $items = $tradeExits->filter(function (TradeExit $tradeExit) use ($positionType): bool {
            $tradePositionType = $tradeExit->trade->position_type;

            if ($tradePositionType instanceof PositionType) {
                return $tradePositionType === $positionType;
            }

            return $tradePositionType === $positionType->value;
        });

        $total = $items->count();

        if ($total === 0) {
            return [
                'total' => 0,
                'profit' => 0,
                'loss' => 0,
                'win_rate' => 0,
                'profit_rate' => 0,
                'loss_rate' => 0,
            ];
        }

        $profit = $items->where('net_profit', '>', 0)->count();
        $loss = $items->where('net_profit', '<', 0)->count();

        return [
            'total' => $total,
            'profit' => $profit,
            'loss' => $loss,
            'win_rate' => ($profit / $total) * 100,
            'profit_rate' => ($profit / $total) * 100,
            'loss_rate' => ($loss / $total) * 100,
        ];
    }

    private function maxConsecutive(Collection $values, string $type): int
    {
        $max = 0;
        $current = 0;

        foreach ($values as $value) {
            $isMatch = match ($type) {
                'profit' => $value > 0,
                'loss' => $value < 0,
                default => false,
            };

            if ($isMatch) {
                $current++;
                $max = max($max, $current);
            } else {
                $current = 0;
            }
        }

        return $max;
    }
}
