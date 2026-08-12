<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planting_recommendations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('farm_id')
                ->constrained('farms')
                ->cascadeOnDelete();

            $table->decimal('score', 5, 2);

            $table->string('status', 30);

            $table->date('start_date');

            $table->date('end_date');

            $table->json('factors_json');

            $table->string('algorithm_version', 50);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planting_recommendations');
    }
};