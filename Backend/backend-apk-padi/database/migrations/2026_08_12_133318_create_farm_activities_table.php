<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farm_activities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('crop_season_id')
                ->constrained('crop_seasons')
                ->cascadeOnDelete();

            $table->enum('type', [
                'land_preparation',
                'planting',
                'fertilizing',
                'spraying',
                'irrigation',
                'other',
            ]);

            $table->timestamp('occurred_at');

            $table->text('notes')->nullable();

            $table->unsignedBigInteger('cost')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farm_activities');
    }
};