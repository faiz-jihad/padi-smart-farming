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
        Schema::create('purchase_contracts', function (Blueprint $table) {
            $table->id();
                
            $table->foreignId('listing_id')
                ->constrained('market_listings')
                ->cascadeOnDelete();

            $table->foreignId('farmer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('partner_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('offer_id')
                ->nullable()
                ->constrained('market_offers')
                ->nullOnDelete();

            $table->decimal('quantity', 12, 2);

            $table->decimal('agreed_price', 15, 2);

            $table->decimal('total_amount', 15, 2);

            $table->string('status', 30)->default('active');

            $table->timestamp('contracted_at');

             $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_contracts');
    }
};
