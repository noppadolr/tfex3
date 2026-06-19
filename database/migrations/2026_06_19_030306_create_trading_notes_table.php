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
        Schema::create('trading_notes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('trade_id')
                ->nullable()
                ->constrained('trades')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('title', 150);
            $table->text('content')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('trade_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trading_notes');
    }
};
