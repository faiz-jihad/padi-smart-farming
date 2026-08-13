<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rice_varieties', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100)->unique();

            $table->text('description')->nullable();

            $table->unsignedSmallInteger('duration_days')->nullable();

            $table->string('source_reference', 255)->nullable();

            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rice_varieties');
    }
};