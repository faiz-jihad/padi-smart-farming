<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('irrigation_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('farm_id')
                ->constrained('farms')
                ->cascadeOnDelete();

            $table->date('schedule_date');

            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->enum('status', [
                'scheduled',
                'completed',
                'cancelled',
            ])->default('scheduled');

            $table->string('source')->default('manual');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index([
                'farm_id',
                'schedule_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('irrigation_schedules');
    }
};