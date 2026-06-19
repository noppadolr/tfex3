<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\CommissionRate;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;

class CommissionRateRepository
{
    public function active(?CarbonInterface $date = null): ?CommissionRate
    {
        $date ??= now();

        return CommissionRate::query()
            ->where('is_active', true)
            ->where(function ($query) use ($date): void {
                $query
                    ->whereNull('effective_from')
                    ->orWhereDate('effective_from', '<=', $date);
            })
            ->where(function ($query) use ($date): void {
                $query
                    ->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $date);
            })
            ->orderByDesc('effective_from')
            ->first();
    }

    public function create(array $data): CommissionRate
    {
        return CommissionRate::query()->create($data);
    }

    public function update(CommissionRate $commissionRate, array $data): bool
    {
        return $commissionRate->update($data);
    }

    public function findOrFail(int $id): CommissionRate
    {
        return CommissionRate::query()->findOrFail($id);
    }

    public function allActive(): Collection
    {
        return CommissionRate::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function latest(): ?CommissionRate
    {
        return CommissionRate::query()
            ->where('is_active', true)
            ->latest()
            ->first();
    }

    public function delete(CommissionRate $commissionRate): bool
    {
        return (bool) $commissionRate->delete();
    }
}
