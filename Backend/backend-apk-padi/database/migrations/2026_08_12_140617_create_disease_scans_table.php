<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disease_scans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('farmer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('farm_id')
                ->constrained('farms')
                ->cascadeOnDelete();

            $table->string('image_url', 500);

            $table->string('image_hash', 64)->nullable();

            $table->string('quality_status', 30);

            $table->string('predicted_class', 100)->nullable();

            $table->decimal('confidence', 5, 4)->nullable();

            $table->string('model_version', 50)->nullable();

            $table->timestamp('scanned_at');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disease_scans');
    }
};