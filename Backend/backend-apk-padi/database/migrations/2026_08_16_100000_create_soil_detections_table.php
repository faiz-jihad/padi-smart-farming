<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soil_detections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained('farms')->cascadeOnDelete();
            $table->string('sample_code', 50)->unique();
            $table->decimal('ph_level', 4, 2)->default(6.5);
            $table->decimal('nitrogen_ppm', 6, 2)->default(120.0);
            $table->decimal('phosphorus_ppm', 6, 2)->default(25.0);
            $table->decimal('potassium_ppm', 6, 2)->default(150.0);
            $table->decimal('moisture_percentage', 5, 2)->default(50.0);
            $table->decimal('organic_matter_percentage', 4, 2)->default(2.5);
            $table->decimal('soil_temp_celsius', 5, 2)->nullable();
            $table->string('soil_type', 50)->default('loam'); // alluvial, clay, loam, sandy_loam, peat, latosol
            $table->integer('soil_health_score')->default(80); // 0 - 100
            $table->string('soil_status', 30)->default('optimal'); // optimal, warning, critical, needs_fertilizer
            $table->json('recommendations_json')->nullable();
            $table->dateTime('tested_at');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soil_detections');
    }
};
