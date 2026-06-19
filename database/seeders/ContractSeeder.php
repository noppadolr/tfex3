<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Contract;
use Illuminate\Database\Seeder;

class ContractSeeder extends Seeder
{
    public function run(): void
    {
        $contracts = [
            [
                'symbol' => 'S50M26',
                'name' => 'SET50 Index Futures Jun 2026',
                'underlying' => 'SET50',
                'multiplier' => 200,
                'tick_size' => 0.10,
                'tick_value' => 20,
                'is_active' => true,
            ],
            [
                'symbol' => 'S50U26',
                'name' => 'SET50 Index Futures Sep 2026',
                'underlying' => 'SET50',
                'multiplier' => 200,
                'tick_size' => 0.10,
                'tick_value' => 20,
                'is_active' => true,
            ],
            [
                'symbol' => 'S50Z26',
                'name' => 'SET50 Index Futures Dec 2026',
                'underlying' => 'SET50',
                'multiplier' => 200,
                'tick_size' => 0.10,
                'tick_value' => 20,
                'is_active' => true,
            ],
        ];

        foreach ($contracts as $contract) {
            Contract::query()->updateOrCreate(
                ['symbol' => $contract['symbol']],
                $contract
            );
        }
    }
}
