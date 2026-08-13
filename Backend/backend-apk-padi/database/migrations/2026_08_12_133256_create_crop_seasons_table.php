<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crop_seasons', function (Blueprint $table) {
            $table->id();

            $table->foreignId('farm_id')
                ->constrained('farms')
                ->cascadeOnDelete();

            $table->foreignId('variety_id')
                ->nullable()
                ->constrained('rice_varieties')
                ->nullOnDelete();

            $table->date('planned_planting_date')->nullable();

            $table->date('planting_date')->nullable();

            $table->date('estimated_harvest_date')->nullable();

            $table->enum('status', [
                'planned',
                'active',
                'completed',
                'cancelled',
            ])->default('planned')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crop_seasons');
    }
};