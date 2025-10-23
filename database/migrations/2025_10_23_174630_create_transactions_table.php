<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('broker_id')->constrained()->cascadeOnDelete();
            
            $table->string('type'); // 'buy', 'sell', 'dividend', 'tax', 'interest'
            $table->decimal('quantity', 20, 8)->nullable();
            $table->decimal('price_per_unit', 18, 6)->nullable();
            $table->decimal('fees', 15, 2)->default(0);
            
            $table->string('currency', 3)->default('EUR');
            $table->decimal('exchange_rate', 18, 6)->default(1);
            $table->decimal('amount_in_base_currency', 18, 2)->nullable();
            
            $table->dateTime('traded_at');
            $table->dateTime('settled_at')->nullable();
            
            $table->string('status')->default('settled'); // 'pending', 'settled', 'cancelled'
            $table->longText('notes')->nullable();
            
            $table->string('tax_lot_id', 36)->nullable();
            $table->decimal('cost_basis', 18, 2)->nullable();
            
            $table->string('external_id', 100)->nullable();
            $table->string('api_provider', 50)->nullable();
            $table->dateTime('imported_at')->nullable();
            
            $table->softDeletes();
            $table->timestamps();
            
            // Indici per performance
            $table->index(['user_id', 'traded_at']);
            $table->index('broker_id');
            $table->index('status');
            $table->unique(['api_provider', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
