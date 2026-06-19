<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\TradingAccount;
use Illuminate\Database\Seeder;

class TradingAccountSeeder extends Seeder
{
    public function run(): void
    {
        TradingAccount::query()->updateOrCreate(
            ['name' => 'TFEX Main Account'],
            [
                'initial_balance' => 100000.00,
                'current_balance' => 100000.00,
                'started_at' => now()->toDateString(),
                'is_active' => true,
            ]
        );
    }
}
