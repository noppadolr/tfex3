<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CommissionSide;
use App\Enums\PositionType;
use App\Enums\TradeStatus;
use App\Models\CommissionRate;
use App\Models\Contract;
use App\Models\EquitySnapshot;
use App\Models\Trade;
use App\Models\TradeCommission;
use App\Models\TradeExit;
use App\Models\TradingAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TradeSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $account = TradingAccount::query()
                ->where('name', 'TFEX Main Account')
                ->firstOrFail();

            $contract = Contract::query()
                ->where('symbol', 'S50M26')
                ->firstOrFail();

            $commissionRate = CommissionRate::query()
                ->where('name', 'TFEX Futures Standard')
                ->firstOrFail();

            $this->createLongTradeExample(
                account: $account,
                contract: $contract,
                commissionRate: $commissionRate
            );

            $this->createShortTradeExample(
                account: $account,
                contract: $contract,
                commissionRate: $commissionRate
            );
        });
    }

    private function createLongTradeExample(
        TradingAccount $account,
        Contract $contract,
        CommissionRate $commissionRate
    ): void {
        $quantity = 10;
        $entryPrice = 900.00;
        $entryDate = now()->subDays(5)->toDateString();

        $entryCommissionAmount = $quantity * (float) $commissionRate->commission_per_contract;
        $entryVatAmount = $entryCommissionAmount * ((float) $commissionRate->vat_rate / 100);
        $entryTotalAmount = $entryCommissionAmount + $entryVatAmount;

        $trade = Trade::query()->create([
            'trading_account_id' => $account->id,
            'contract_id' => $contract->id,
            'trade_date' => $entryDate,
            'position_type' => PositionType::Long->value,
            'quantity' => $quantity,
            'closed_quantity' => 7,
            'remaining_quantity' => 3,
            'entry_price' => $entryPrice,
            'entry_date' => $entryDate,
            'entry_time' => '10:15:00',
            'status' => TradeStatus::PartialClosed->value,
            'total_gross_profit' => 10000.00,
            'total_commission' => 490.00,
            'total_vat' => 34.30,
            'total_net_profit' => 9475.70,
            'note' => 'ตัวอย่าง Long S50M26 จำนวน 10 สัญญา และแบ่งปิดบางส่วน',
        ]);

        TradeCommission::query()->create([
            'trade_id' => $trade->id,
            'trade_exit_id' => null,
            'commission_rate_id' => $commissionRate->id,
            'side' => CommissionSide::Entry->value,
            'quantity' => $quantity,
            'commission_rate' => $commissionRate->commission_per_contract,
            'commission_amount' => $entryCommissionAmount,
            'vat_rate' => $commissionRate->vat_rate,
            'vat_amount' => $entryVatAmount,
            'total_amount' => $entryTotalAmount,
        ]);

        $this->createLongExit(
            trade: $trade,
            account: $account,
            commissionRate: $commissionRate,
            closeQuantity: 3,
            exitPrice: 905.00,
            exitDate: now()->subDays(4)->toDateString(),
            exitTime: '14:30:00'
        );

        $this->createLongExit(
            trade: $trade,
            account: $account,
            commissionRate: $commissionRate,
            closeQuantity: 4,
            exitPrice: 910.00,
            exitDate: now()->subDays(3)->toDateString(),
            exitTime: '11:20:00'
        );
    }

    private function createShortTradeExample(
        TradingAccount $account,
        Contract $contract,
        CommissionRate $commissionRate
    ): void {
        $quantity = 5;
        $entryPrice = 920.00;
        $entryDate = now()->subDays(2)->toDateString();

        $entryCommissionAmount = $quantity * (float) $commissionRate->commission_per_contract;
        $entryVatAmount = $entryCommissionAmount * ((float) $commissionRate->vat_rate / 100);
        $entryTotalAmount = $entryCommissionAmount + $entryVatAmount;

        $trade = Trade::query()->create([
            'trading_account_id' => $account->id,
            'contract_id' => $contract->id,
            'trade_date' => $entryDate,
            'position_type' => PositionType::Short->value,
            'quantity' => $quantity,
            'closed_quantity' => 5,
            'remaining_quantity' => 0,
            'entry_price' => $entryPrice,
            'entry_date' => $entryDate,
            'entry_time' => '10:00:00',
            'status' => TradeStatus::Closed->value,
            'total_gross_profit' => 5000.00,
            'total_commission' => 350.00,
            'total_vat' => 24.50,
            'total_net_profit' => 4625.50,
            'note' => 'ตัวอย่าง Short S50M26 จำนวน 5 สัญญา และปิดครบแล้ว',
        ]);

        TradeCommission::query()->create([
            'trade_id' => $trade->id,
            'trade_exit_id' => null,
            'commission_rate_id' => $commissionRate->id,
            'side' => CommissionSide::Entry->value,
            'quantity' => $quantity,
            'commission_rate' => $commissionRate->commission_per_contract,
            'commission_amount' => $entryCommissionAmount,
            'vat_rate' => $commissionRate->vat_rate,
            'vat_amount' => $entryVatAmount,
            'total_amount' => $entryTotalAmount,
        ]);

        $this->createShortExit(
            trade: $trade,
            account: $account,
            commissionRate: $commissionRate,
            closeQuantity: 5,
            exitPrice: 915.00,
            exitDate: now()->subDay()->toDateString(),
            exitTime: '15:45:00'
        );
    }

    private function createLongExit(
        Trade $trade,
        TradingAccount $account,
        CommissionRate $commissionRate,
        int $closeQuantity,
        float $exitPrice,
        string $exitDate,
        string $exitTime
    ): void {
        $multiplier = (float) $trade->contract->multiplier;

        $grossProfit = ($exitPrice - (float) $trade->entry_price) * $closeQuantity * $multiplier;

        $entryCommissionAllocated = ((float) $trade->commissions()
            ->where('side', CommissionSide::Entry->value)
            ->sum('commission_amount')) * ($closeQuantity / $trade->quantity);

        $entryVatAllocated = ((float) $trade->commissions()
            ->where('side', CommissionSide::Entry->value)
            ->sum('vat_amount')) * ($closeQuantity / $trade->quantity);

        $exitCommissionAmount = $closeQuantity * (float) $commissionRate->commission_per_contract;
        $exitVatAmount = $exitCommissionAmount * ((float) $commissionRate->vat_rate / 100);

        $totalCost = $entryCommissionAllocated
            + $entryVatAllocated
            + $exitCommissionAmount
            + $exitVatAmount;

        $netProfit = $grossProfit - $totalCost;

        $newBalance = (float) $account->current_balance + $netProfit;

        $tradeExit = TradeExit::query()->create([
            'trade_id' => $trade->id,
            'exit_date' => $exitDate,
            'exit_time' => $exitTime,
            'close_quantity' => $closeQuantity,
            'exit_price' => $exitPrice,
            'gross_profit' => round($grossProfit, 2),
            'entry_commission_allocated' => round($entryCommissionAllocated, 2),
            'entry_vat_allocated' => round($entryVatAllocated, 2),
            'exit_commission_amount' => round($exitCommissionAmount, 2),
            'exit_vat_amount' => round($exitVatAmount, 2),
            'total_cost' => round($totalCost, 2),
            'net_profit' => round($netProfit, 2),
            'balance_after_close' => round($newBalance, 2),
            'note' => 'แบ่งปิด Long ตัวอย่าง',
        ]);

        TradeCommission::query()->create([
            'trade_id' => $trade->id,
            'trade_exit_id' => $tradeExit->id,
            'commission_rate_id' => $commissionRate->id,
            'side' => CommissionSide::Exit->value,
            'quantity' => $closeQuantity,
            'commission_rate' => $commissionRate->commission_per_contract,
            'commission_amount' => round($exitCommissionAmount, 2),
            'vat_rate' => $commissionRate->vat_rate,
            'vat_amount' => round($exitVatAmount, 2),
            'total_amount' => round($exitCommissionAmount + $exitVatAmount, 2),
        ]);

        $account->update([
            'current_balance' => round($newBalance, 2),
        ]);

        $this->createEquitySnapshot(
            account: $account,
            snapshotDate: $exitDate,
            netProfit: $netProfit
        );
    }

    private function createShortExit(
        Trade $trade,
        TradingAccount $account,
        CommissionRate $commissionRate,
        int $closeQuantity,
        float $exitPrice,
        string $exitDate,
        string $exitTime
    ): void {
        $multiplier = (float) $trade->contract->multiplier;

        $grossProfit = ((float) $trade->entry_price - $exitPrice) * $closeQuantity * $multiplier;

        $entryCommissionAllocated = ((float) $trade->commissions()
            ->where('side', CommissionSide::Entry->value)
            ->sum('commission_amount')) * ($closeQuantity / $trade->quantity);

        $entryVatAllocated = ((float) $trade->commissions()
            ->where('side', CommissionSide::Entry->value)
            ->sum('vat_amount')) * ($closeQuantity / $trade->quantity);

        $exitCommissionAmount = $closeQuantity * (float) $commissionRate->commission_per_contract;
        $exitVatAmount = $exitCommissionAmount * ((float) $commissionRate->vat_rate / 100);

        $totalCost = $entryCommissionAllocated
            + $entryVatAllocated
            + $exitCommissionAmount
            + $exitVatAmount;

        $netProfit = $grossProfit - $totalCost;

        $newBalance = (float) $account->current_balance + $netProfit;

        $tradeExit = TradeExit::query()->create([
            'trade_id' => $trade->id,
            'exit_date' => $exitDate,
            'exit_time' => $exitTime,
            'close_quantity' => $closeQuantity,
            'exit_price' => $exitPrice,
            'gross_profit' => round($grossProfit, 2),
            'entry_commission_allocated' => round($entryCommissionAllocated, 2),
            'entry_vat_allocated' => round($entryVatAllocated, 2),
            'exit_commission_amount' => round($exitCommissionAmount, 2),
            'exit_vat_amount' => round($exitVatAmount, 2),
            'total_cost' => round($totalCost, 2),
            'net_profit' => round($netProfit, 2),
            'balance_after_close' => round($newBalance, 2),
            'note' => 'ปิด Short ตัวอย่างครบจำนวน',
        ]);

        TradeCommission::query()->create([
            'trade_id' => $trade->id,
            'trade_exit_id' => $tradeExit->id,
            'commission_rate_id' => $commissionRate->id,
            'side' => CommissionSide::Exit->value,
            'quantity' => $closeQuantity,
            'commission_rate' => $commissionRate->commission_per_contract,
            'commission_amount' => round($exitCommissionAmount, 2),
            'vat_rate' => $commissionRate->vat_rate,
            'vat_amount' => round($exitVatAmount, 2),
            'total_amount' => round($exitCommissionAmount + $exitVatAmount, 2),
        ]);

        $account->update([
            'current_balance' => round($newBalance, 2),
        ]);

        $this->createEquitySnapshot(
            account: $account,
            snapshotDate: $exitDate,
            netProfit: $netProfit
        );
    }

    private function createEquitySnapshot(
        TradingAccount $account,
        string $snapshotDate,
        float $netProfit
    ): void {
        $balance = (float) $account->fresh()->current_balance;
        $initialBalance = (float) $account->initial_balance;

        $drawdown = max(0, $initialBalance - $balance);
        $drawdownPercent = $initialBalance > 0
            ? ($drawdown / $initialBalance) * 100
            : 0;

        EquitySnapshot::query()->updateOrCreate(
            [
                'trading_account_id' => $account->id,
                'snapshot_date' => $snapshotDate,
            ],
            [
                'balance' => round($balance, 2),
                'equity' => round($balance, 2),
                'net_profit' => round($netProfit, 2),
                'drawdown' => round($drawdown, 2),
                'drawdown_percent' => round($drawdownPercent, 2),
            ]
        );
    }
}
