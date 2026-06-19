<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CommissionRate;
use Illuminate\Database\Seeder;

class CommissionRateSeeder extends Seeder
{
    public function run(): void
    {
        CommissionRate::query()->updateOrCreate(
            ['name' => 'TFEX Futures Standard'],
            [
                'commission_per_contract' => 35.00,
                'vat_rate' => 7.00,
                'effective_from' => now()->startOfYear()->toDateString(),
                'effective_to' => null,
                'is_active' => true,
            ]
        );
    }
}
