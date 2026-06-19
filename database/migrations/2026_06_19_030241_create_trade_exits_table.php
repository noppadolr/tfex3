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
        Schema::create('trade_exits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('trade_id')
                ->constrained('trades')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->date('exit_date');
            $table->time('exit_time')->nullable();

            $table->integer('close_quantity')->default(0);
            $table->decimal('exit_price', 10, 2);

            $table->decimal('gross_profit', 15, 2)->default(0);
            $table->decimal('entry_commission_allocated', 15, 2)->default(0);
            $table->decimal('entry_vat_allocated', 15, 2)->default(0);
            $table->decimal('exit_commission_amount', 15, 2)->default(0);
            $table->decimal('exit_vat_amount', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->decimal('net_profit', 15, 2)->default(0);

            $table->decimal('balance_after_close', 15, 2)->nullable();

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('exit_date');
            $table->index('trade_id');
            $table->index('net_profit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_exits');
    }
};
