<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\TradeData;
use App\Models\Trade;
use App\Repositories\TradeCommissionRepository;
use App\Repositories\TradeRepository;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TradeService
{
    public function __construct(
        private readonly TradeRepository $tradeRepository,
        private readonly CommissionService $commissionService,
        private readonly TradeCommissionRepository $tradeCommissionRepository,
    ) {}

    public function create(TradeData $data): Trade
    {
        return DB::transaction(function () use ($data): Trade {
            if ($data->quantity <= 0) {
                throw new RuntimeException('จำนวนสัญญาต้องมากกว่า 0');
            }

            if ($data->entryPrice <= 0) {
                throw new RuntimeException('ราคาเข้าต้องมากกว่า 0');
            }

            $trade = $this->tradeRepository->create($data);

            $entryCommission = $this->commissionService->calculateEntryCommission(
                quantity: $data->quantity,
                tradeDate: $data->tradeDate,
            );

            $this->tradeCommissionRepository->createForTrade(
                trade: $trade,
                data: $entryCommission,
            );

            return $this->tradeRepository->updateSummary($trade);
        });
    }

    public function update(Trade $trade, TradeData $data): Trade
    {
        return DB::transaction(function () use ($trade, $data): Trade {
            if ($trade->closed_quantity > 0) {
                throw new RuntimeException('รายการที่มีการปิดสัญญาแล้ว ไม่ควรแก้ไขข้อมูลเปิดสัญญาโดยตรง');
            }

            if ($data->quantity <= 0) {
                throw new RuntimeException('จำนวนสัญญาต้องมากกว่า 0');
            }

            if ($data->entryPrice <= 0) {
                throw new RuntimeException('ราคาเข้าต้องมากกว่า 0');
            }

            $this->tradeRepository->update($trade, $data);

            return $trade->refresh();
        });
    }

    public function delete(Trade $trade): bool
    {
        return DB::transaction(function () use ($trade): bool {
            if ($trade->closed_quantity > 0) {
                throw new RuntimeException('ไม่สามารถลบรายการที่มีการปิดสัญญาแล้ว');
            }

            return $this->tradeRepository->delete($trade);
        });
    }
}
