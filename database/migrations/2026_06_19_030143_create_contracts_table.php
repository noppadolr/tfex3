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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();

            $table->string('symbol', 50)->unique(); // S50M26
            $table->string('name', 150)->nullable();
            $table->string('underlying', 100)->nullable(); // SET50

            $table->decimal('multiplier', 10, 2)->default(200);
            $table->decimal('tick_size', 10, 2)->default(0.10);
            $table->decimal('tick_value', 10, 2)->default(20);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index('symbol');
            $table->index('underlying');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
