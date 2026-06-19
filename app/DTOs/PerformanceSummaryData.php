<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class PerformanceSummaryData
{
    public function __construct(
        public float $initialBalance,
        public float $currentBalance,
        public float $totalNetProfit,
        public float $totalNetProfitPercent,
        public float $grossProfit,
        public float $grossLoss,
        public float $absoluteDrawdown,
        public float $maximalDrawdown,
        public float $expectedPayoff,
        public int $totalTrade,
        public int $totalTradeExit,
        public float $longWinRate,
        public float $profitTradesOfTotalLong,
        public float $shortWinRate,
        public float $lossTradesOfTotalShort,
        public float $largestProfitTrade,
        public float $averageProfitTrade,
        public int $maximalConsecutiveProfitTimes,
        public int $maximalConsecutiveLossTimes,
        public float $totalCommission,
        public float $totalVat,
        public float $totalCost,
    ) {}

    public static function empty(float $initialBalance = 0): self
    {
        return new self(
            initialBalance: $initialBalance,
            currentBalance: $initialBalance,
            totalNetProfit: 0,
            totalNetProfitPercent: 0,
            grossProfit: 0,
            grossLoss: 0,
            absoluteDrawdown: 0,
            maximalDrawdown: 0,
            expectedPayoff: 0,
            totalTrade: 0,
            totalTradeExit: 0,
            longWinRate: 0,
            profitTradesOfTotalLong: 0,
            shortWinRate: 0,
            lossTradesOfTotalShort: 0,
            largestProfitTrade: 0,
            averageProfitTrade: 0,
            maximalConsecutiveProfitTimes: 0,
            maximalConsecutiveLossTimes: 0,
            totalCommission: 0,
            totalVat: 0,
            totalCost: 0,
        );
    }

    public function toArray(): array
    {
        return [
            'initial_balance' => round($this->initialBalance, 2),
            'current_balance' => round($this->currentBalance, 2),
            'total_net_profit' => round($this->totalNetProfit, 2),
            'total_net_profit_percent' => round($this->totalNetProfitPercent, 2),
            'gross_profit' => round($this->grossProfit, 2),
            'gross_loss' => round($this->grossLoss, 2),
            'absolute_drawdown' => round($this->absoluteDrawdown, 2),
            'maximal_drawdown' => round($this->maximalDrawdown, 2),
            'expected_payoff' => round($this->expectedPayoff, 2),
            'total_trade' => $this->totalTrade,
            'total_trade_exit' => $this->totalTradeExit,
            'long_win_rate' => round($this->longWinRate, 2),
            'profit_trades_of_total_long' => round($this->profitTradesOfTotalLong, 2),
            'short_win_rate' => round($this->shortWinRate, 2),
            'loss_trades_of_total_short' => round($this->lossTradesOfTotalShort, 2),
            'largest_profit_trade' => round($this->largestProfitTrade, 2),
            'average_profit_trade' => round($this->averageProfitTrade, 2),
            'maximal_consecutive_profit_times' => $this->maximalConsecutiveProfitTimes,
            'maximal_consecutive_loss_times' => $this->maximalConsecutiveLossTimes,
            'total_commission' => round($this->totalCommission, 2),
            'total_vat' => round($this->totalVat, 2),
            'total_cost' => round($this->totalCost, 2),
        ];
    }
}
