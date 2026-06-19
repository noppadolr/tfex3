<?php

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
        Schema::create('equity_snapshots', function (Blueprint $table) {
            $table->id();

            $table->foreignId('trading_account_id')
                ->constrained('trading_accounts')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->date('snapshot_date');

            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('equity', 15, 2)->default(0);
            $table->decimal('net_profit', 15, 2)->default(0);

            $table->decimal('drawdown', 15, 2)->default(0);
            $table->decimal('drawdown_percent', 10, 2)->default(0);

            $table->timestamps();

            $table->index('snapshot_date');
            $table->index('trading_account_id');
            $table->unique(['trading_account_id', 'snapshot_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equity_snapshots');
    }
};
