<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('farmer_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('name', 100);

            $table->decimal('area_ha', 10, 4);

            $table->decimal('latitude', 10, 7);

            $table->decimal('longitude', 10, 7);

            $table->string('irrigation_type', 50);

            $table->text('irrigation_notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farms');
    }
};