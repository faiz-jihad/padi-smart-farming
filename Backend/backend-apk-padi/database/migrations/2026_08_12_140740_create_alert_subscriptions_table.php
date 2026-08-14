<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('farmer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('farm_id')
                ->constrained('farms')
                ->cascadeOnDelete();

            $table->boolean('is_active')->default(true);

            $table->decimal('radius_km', 6, 2);

            $table->timestamps();

            $table->unique(['farmer_id', 'farm_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_subscriptions');
    }
};