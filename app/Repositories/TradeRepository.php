<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTOs\TradeData;
use App\Enums\TradeStatus;
use App\Models\Trade;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TradeRepository
{
    public function create(TradeData $data): Trade
    {
        return Trade::query()->create([
            ...$data->toArray(),
            'status' => TradeStatus::Open->value,
            'total_gross_profit' => 0,
            'total_commission' => 0,
            'total_vat' => 0,
            'total_net_profit' => 0,
        ]);
    }

    public function update(Trade $trade, TradeData $data): bool
    {
        return $trade->update($data->toArray());
    }

    public function find(int $id): ?Trade
    {
        return Trade::query()
            ->with([
                'tradingAccount',
                'contract',
                'exits',
                'commissions',
            ])
            ->find($id);
    }

    public function findOrFail(int $id): Trade
    {
        return Trade::query()
            ->with([
                'tradingAccount',
                'contract',
                'exits',
                'commissions',
            ])
            ->findOrFail($id);
    }

    public function paginate(
        string $search = '',
        string $sortField = 'trade_date',
        string $sortDirection = 'desc',
        int $perPage = 10
    ): LengthAwarePaginator {
        return Trade::query()
            ->with([
                'tradingAccount',
                'contract',
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('position_type', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhereHas('contract', function ($query) use ($search): void {
                            $query->where('symbol', 'like', "%{$search}%");
                        })
                        ->orWhereHas('tradingAccount', function ($query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate($perPage);
    }

    public function getClosedTrades(): Collection
    {
        return Trade::query()
            ->with(['tradingAccount', 'contract', 'exits'])
            ->where('status', TradeStatus::Closed->value)
            ->orderByDesc('trade_date')
            ->get();
    }

    public function updateQuantitiesAndStatus(Trade $trade): Trade
    {
        $closedQuantity = (int) $trade->exits()->sum('close_quantity');
        $remainingQuantity = max(0, (int) $trade->quantity - $closedQuantity);

        $status = match (true) {
            $remainingQuantity === 0 => TradeStatus::Closed,
            $closedQuantity > 0 => TradeStatus::PartialClosed,
            default => TradeStatus::Open,
        };

        $trade->update([
            'closed_quantity' => $closedQuantity,
            'remaining_quantity' => $remainingQuantity,
            'status' => $status->value,
        ]);

        return $trade->refresh();
    }

    public function updateSummary(Trade $trade): Trade
    {
        $totalGrossProfit = (float) $trade->exits()->sum('gross_profit');
        $totalNetProfit = (float) $trade->exits()->sum('net_profit');

        $totalCommission = (float) $trade->commissions()->sum('commission_amount');
        $totalVat = (float) $trade->commissions()->sum('vat_amount');

        $trade->update([
            'total_gross_profit' => round($totalGrossProfit, 2),
            'total_commission' => round($totalCommission, 2),
            'total_vat' => round($totalVat, 2),
            'total_net_profit' => round($totalNetProfit, 2),
        ]);

        return $trade->refresh();
    }

    public function delete(Trade $trade): bool
    {
        return (bool) $trade->delete();
    }
}
