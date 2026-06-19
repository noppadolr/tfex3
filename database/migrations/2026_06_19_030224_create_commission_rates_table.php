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
        Schema::create('commission_rates', function (Blueprint $table) {
            $table->id();

            $table->string('name', 150); // TFEX Futures Standard
            $table->decimal('commission_per_contract', 10, 2)->default(0);
            $table->decimal('vat_rate', 5, 2)->default(7.00);

            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index(['effective_from', 'effective_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_rates');
    }
};
