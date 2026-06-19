<?php

use App\Enums\PositionType;
use App\Enums\TradeStatus;
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
        Schema::create('trades', function (Blueprint $table) {
            $table->id();

            $table->foreignId('trading_account_id')
                ->constrained('trading_accounts')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('contract_id')
                ->constrained('contracts')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->date('trade_date');

            $table->enum('position_type', [
                PositionType::Long->value,
                PositionType::Short->value,
            ]);

            $table->integer('quantity')->default(0);
            $table->integer('closed_quantity')->default(0);
            $table->integer('remaining_quantity')->default(0);

            $table->decimal('entry_price', 10, 2);
            $table->date('entry_date');
            $table->time('entry_time')->nullable();

            $table->enum('status', [
                TradeStatus::Open->value,
                TradeStatus::PartialClosed->value,
                TradeStatus::Closed->value,
            ])->default(TradeStatus::Open->value);

            $table->decimal('total_gross_profit', 15, 2)->default(0);
            $table->decimal('total_commission', 15, 2)->default(0);
            $table->decimal('total_vat', 15, 2)->default(0);
            $table->decimal('total_net_profit', 15, 2)->default(0);

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('trade_date');
            $table->index('entry_date');
            $table->index('position_type');
            $table->index('status');
            $table->index(['trading_account_id', 'status']);
            $table->index(['contract_id', 'trade_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
