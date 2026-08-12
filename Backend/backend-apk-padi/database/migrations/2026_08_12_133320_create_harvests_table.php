<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harvests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('crop_season_id')
                ->constrained('crop_seasons')
                ->cascadeOnDelete();

            $table->date('harvest_date');

            $table->decimal('quantity', 12, 2);

            $table->string('unit', 20);

            $table->string('quality_grade', 50)->nullable();

            $table->decimal('moisture_percent', 5, 2)->nullable();

            $table->enum('verification_status', [
                'unverified',
                'verified_ppl',
            ])->default('unverified');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harvests');
    }
};