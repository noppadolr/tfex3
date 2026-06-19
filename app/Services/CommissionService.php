<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\CommissionData;
use App\Enums\CommissionSide;
use App\Models\CommissionRate;
use App\Models\Trade;
use App\Repositories\CommissionRateRepository;
use App\Repositories\TradeCommissionRepository;
use Carbon\CarbonInterface;
use RuntimeException;

class CommissionService
{
    public function __construct(
        private readonly CommissionRateRepository $commissionRateRepository,
        private readonly TradeCommissionRepository $tradeCommissionRepository,
    ) {}

    public function calculateEntryCommission(
        int $quantity,
        CarbonInterface|string|null $tradeDate = null,
    ): CommissionData {
        return $this->calculate(
            side: CommissionSide::Entry,
            quantity: $quantity,
            tradeDate: $tradeDate,
        );
    }

    public function calculateExitCommission(
        int $quantity,
        CarbonInterface|string|null $tradeDate = null,
    ): CommissionData {
        return $this->calculate(
            side: CommissionSide::Exit,
            quantity: $quantity,
            tradeDate: $tradeDate,
        );
    }

    public function calculate(
        CommissionSide $side,
        int $quantity,
        CarbonInterface|string|null $tradeDate = null,
    ): CommissionData {
        if ($quantity <= 0) {
            throw new RuntimeException('จำนวนสัญญาต้องมากกว่า 0');
        }

        $rate = $this->getActiveRate($tradeDate);

        $commissionRate = (float) $rate->commission_per_contract;
        $vatRate = (float) $rate->vat_rate;

        $commissionAmount = $quantity * $commissionRate;
        $vatAmount = $commissionAmount * ($vatRate / 100);
        $totalAmount = $commissionAmount + $vatAmount;

        return new CommissionData(
            commissionRateId: $rate->id,
            side: $side,
            quantity: $quantity,
            commissionRate: round($commissionRate, 2),
            commissionAmount: round($commissionAmount, 2),
            vatRate: round($vatRate, 2),
            vatAmount: round($vatAmount, 2),
            totalAmount: round($totalAmount, 2),
        );
    }

    public function getActiveRate(
        CarbonInterface|string|null $tradeDate = null,
    ): CommissionRate {
        $date = $tradeDate
            ? now()->parse($tradeDate)
            : now();

        $rate = $this->commissionRateRepository->active($date);

        if (! $rate) {
            throw new RuntimeException('ไม่พบอัตราค่าคอมมิชชั่นที่เปิดใช้งาน');
        }

        return $rate;
    }

    public function calculateAllocatedEntryCommission(
        Trade $trade,
        int $closeQuantity,
    ): array {
        if ($trade->quantity <= 0) {
            throw new RuntimeException('จำนวนสัญญาเริ่มต้นไม่ถูกต้อง');
        }

        if ($closeQuantity <= 0) {
            throw new RuntimeException('จำนวนสัญญาที่ปิดต้องมากกว่า 0');
        }

        $entryCommissionTotal = $this->tradeCommissionRepository
            ->entryCommissionTotal($trade);

        $entryVatTotal = $this->tradeCommissionRepository
            ->entryVatTotal($trade);

        $ratio = $closeQuantity / (int) $trade->quantity;

        return [
            'entry_commission_allocated' => round($entryCommissionTotal * $ratio, 2),
            'entry_vat_allocated' => round($entryVatTotal * $ratio, 2),
        ];
    }
}
