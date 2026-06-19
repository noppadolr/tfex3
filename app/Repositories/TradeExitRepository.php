<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Trade;
use App\Models\TradeExit;
use Illuminate\Database\Eloquent\Collection;

class TradeExitRepository
{
    public function create(array $data): TradeExit
    {
        return TradeExit::query()->create($data);
    }

    public function find(int $id): ?TradeExit
    {
        return TradeExit::query()
            ->with(['trade.contract', 'trade.tradingAccount', 'commissions'])
            ->find($id);
    }

    public function findOrFail(int $id): TradeExit
    {
        return TradeExit::query()
            ->with(['trade.contract', 'trade.tradingAccount', 'commissions'])
            ->findOrFail($id);
    }

    public function getByTrade(Trade $trade): Collection
    {
        return TradeExit::query()
            ->where('trade_id', $trade->id)
            ->orderBy('exit_date')
            ->orderBy('exit_time')
            ->get();
    }

    public function getClosedResultsOrdered(): Collection
    {
        return TradeExit::query()
            ->with(['trade.contract'])
            ->orderBy('exit_date')
            ->orderBy('exit_time')
            ->get();
    }

    public function totalNetProfit(): float
    {
        return (float) TradeExit::query()->sum('net_profit');
    }

    public function grossProfit(): float
    {
        return (float) TradeExit::query()
            ->where('net_profit', '>', 0)
            ->sum('net_profit');
    }

    public function grossLoss(): float
    {
        return (float) TradeExit::query()
            ->where('net_profit', '<', 0)
            ->sum('net_profit');
    }

    public function largestProfitTrade(): float
    {
        return (float) TradeExit::query()
            ->where('net_profit', '>', 0)
            ->max('net_profit');
    }

    public function averageProfitTrade(): float
    {
        return (float) TradeExit::query()
            ->where('net_profit', '>', 0)
            ->avg('net_profit');
    }

    public function count(): int
    {
        return TradeExit::query()->count();
    }

    public function delete(TradeExit $tradeExit): bool
    {
        return (bool) $tradeExit->delete();
    }
}
