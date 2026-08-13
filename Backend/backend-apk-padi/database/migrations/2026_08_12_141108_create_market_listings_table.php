<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_listings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('farmer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('farm_id')
                ->constrained('farms')
                ->cascadeOnDelete();

            $table->foreignId('crop_season_id')
                ->constrained('crop_seasons')
                ->cascadeOnDelete();

            $table->foreignId('harvest_id')
                ->nullable()
                ->constrained('harvests')
                ->nullOnDelete();

            $table->string('commodity', 100);

            $table->decimal('quantity', 12, 2);

            $table->string('unit', 20);

            $table->decimal('price_per_unit', 15, 2);

            $table->text('description')->nullable();

            $table->string('status', 30)->default('draft');

            $table->timestamp('published_at')->nullable();

            $table->timestamp('expires_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_listings');
    }
};