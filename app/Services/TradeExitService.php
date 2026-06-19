<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\TradeExitData;
use App\Enums\PositionType;
use App\Models\TradeExit;
use App\Repositories\TradeCommissionRepository;
use App\Repositories\TradeExitRepository;
use App\Repositories\TradeRepository;
use App\Repositories\TradingAccountRepository;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TradeExitService
{
    public function __construct(
        private readonly TradeRepository $tradeRepository,
        private readonly TradeExitRepository $tradeExitRepository,
        private readonly TradingAccountRepository $tradingAccountRepository,
        private readonly TradeCommissionRepository $tradeCommissionRepository,
        private readonly CommissionService $commissionService,
        private readonly EquitySnapshotService $equitySnapshotService,
    ) {}

    public function close(TradeExitData $data): TradeExit
    {
        return DB::transaction(function () use ($data): TradeExit {
            $trade = $this->tradeRepository->findOrFail($data->tradeId);

            $this->validateCloseData($trade->remaining_quantity, $data);

            $grossProfit = $this->calculateGrossProfit(
                positionType: $trade->position_type instanceof PositionType
                    ? $trade->position_type
                    : PositionType::from($trade->position_type),
                entryPrice: (float) $trade->entry_price,
                exitPrice: $data->exitPrice,
                quantity: $data->closeQuantity,
                multiplier: (float) $trade->contract->multiplier,
            );

            $allocatedEntry = $this->commissionService
                ->calculateAllocatedEntryCommission(
                    trade: $trade,
                    closeQuantity: $data->closeQuantity,
                );

            $exitCommission = $this->commissionService->calculateExitCommission(
                quantity: $data->closeQuantity,
                tradeDate: $data->exitDate,
            );

            $totalCost = $allocatedEntry['entry_commission_allocated']
                + $allocatedEntry['entry_vat_allocated']
                + $exitCommission->commissionAmount
                + $exitCommission->vatAmount;

            $netProfit = $grossProfit - $totalCost;

            $account = $trade->tradingAccount;

            $account = $this->tradingAccountRepository->increaseBalance(
                account: $account,
                amount: $netProfit,
            );

            $tradeExit = $this->tradeExitRepository->create([
                'trade_id' => $trade->id,
                'exit_date' => $data->exitDate,
                'exit_time' => $data->exitTime,
                'close_quantity' => $data->closeQuantity,
                'exit_price' => round($data->exitPrice, 2),

                'gross_profit' => round($grossProfit, 2),

                'entry_commission_allocated' => $allocatedEntry['entry_commission_allocated'],
                'entry_vat_allocated' => $allocatedEntry['entry_vat_allocated'],

                'exit_commission_amount' => round($exitCommission->commissionAmount, 2),
                'exit_vat_amount' => round($exitCommission->vatAmount, 2),

                'total_cost' => round($totalCost, 2),
                'net_profit' => round($netProfit, 2),

                'balance_after_close' => round((float) $account->current_balance, 2),

                'note' => $data->note,
            ]);

            $this->tradeCommissionRepository->createForTradeExit(
                trade: $trade,
                tradeExit: $tradeExit,
                data: $exitCommission,
            );

            $this->tradeRepository->updateQuantitiesAndStatus($trade);
            $this->tradeRepository->updateSummary($trade);

            $this->equitySnapshotService->createAfterTradeExit(
                account: $account,
                snapshotDate: (string) $data->exitDate,
                netProfit: $netProfit,
            );

            return $tradeExit->refresh();
        });
    }

    private function validateCloseData(
        int $remainingQuantity,
        TradeExitData $data,
    ): void {
        if ($data->closeQuantity <= 0) {
            throw new RuntimeException('จำนวนสัญญาที่ปิดต้องมากกว่า 0');
        }

        if ($data->closeQuantity > $remainingQuantity) {
            throw new RuntimeException('จำนวนสัญญาที่ปิดมากกว่าจำนวนคงเหลือ');
        }

        if ($data->exitPrice <= 0) {
            throw new RuntimeException('ราคาออกต้องมากกว่า 0');
        }
    }

    private function calculateGrossProfit(
        PositionType $positionType,
        float $entryPrice,
        float $exitPrice,
        int $quantity,
        float $multiplier,
    ): float {
        return match ($positionType) {
            PositionType::Long => ($exitPrice - $entryPrice) * $quantity * $multiplier,
            PositionType::Short => ($entryPrice - $exitPrice) * $quantity * $multiplier,
        };
    }
}
