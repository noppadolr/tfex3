<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\TradingAccount;
use Illuminate\Database\Eloquent\Collection;

class TradingAccountRepository
{
    public function defaultAccount(): ?TradingAccount
    {
        return TradingAccount::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->first();
    }

    public function findOrFail(int $id): TradingAccount
    {
        return TradingAccount::query()->findOrFail($id);
    }

    public function allActive(): Collection
    {
        return TradingAccount::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): TradingAccount
    {
        return TradingAccount::query()->create($data);
    }

    public function update(TradingAccount $account, array $data): bool
    {
        return $account->update($data);
    }

    public function increaseBalance(
        TradingAccount $account,
        float $amount
    ): TradingAccount {
        $account->increment('current_balance', round($amount, 2));

        return $account->refresh();
    }

    public function decreaseBalance(
        TradingAccount $account,
        float $amount
    ): TradingAccount {
        $account->decrement('current_balance', round($amount, 2));

        return $account->refresh();
    }

    public function updateBalance(
        TradingAccount $account,
        float $balance
    ): TradingAccount {
        $account->update([
            'current_balance' => round($balance, 2),
        ]);

        return $account->refresh();
    }
}
