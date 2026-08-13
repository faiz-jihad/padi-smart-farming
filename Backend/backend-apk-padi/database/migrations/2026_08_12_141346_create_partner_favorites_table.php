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
        Schema::create('partner_favorites', function (Blueprint $table) {
            $table->id();

            $table->foreignId('partner_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('listing_id')
                ->constrained('market_listings')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['partner_id', 'listing_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_favorites');
    }
};
