<?php

use App\Enums\CommissionSide;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trade_commissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('trade_id')
                ->constrained('trades')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('trade_exit_id')
                ->nullable()
                ->constrained('trade_exits')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('commission_rate_id')
                ->nullable()
                ->constrained('commission_rates')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->enum('side', [
                CommissionSide::Entry->value,
                CommissionSide::Exit->value,
            ]);

            $table->integer('quantity')->default(0);

            $table->decimal('commission_rate', 10, 2)->default(0);
            $table->decimal('commission_amount', 15, 2)->default(0);

            $table->decimal('vat_rate', 5, 2)->default(7.00);
            $table->decimal('vat_amount', 15, 2)->default(0);

            $table->decimal('total_amount', 15, 2)->default(0);

            $table->timestamps();

            $table->index('side');
            $table->index('trade_id');
            $table->index('trade_exit_id');
            $table->index('commission_rate_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_commissions');
    }
};
