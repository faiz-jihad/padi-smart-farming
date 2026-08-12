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
        Schema::create('contract_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contract_id')
                ->constrained('purchase_contracts')
                ->cascadeOnDelete();

            $table->decimal('amount', 15, 2);

            $table->string('payment_method', 50)->nullable();

            $table->string('status', 30)->default('pending');

            $table->string('reference', 100)->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_payments');
    }
};
