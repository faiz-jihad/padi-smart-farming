<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_snapshots', function (Blueprint $table) {
            $table->id();

            $table->foreignId('farm_id')
                ->constrained('farms')
                ->cascadeOnDelete();

            $table->string('provider', 50);

            $table->timestamp('observed_at');

            $table->json('payload_json');

            $table->timestamp('expires_at');

            $table->timestamps();

            $table->unique(
                ['farm_id', 'observed_at'],
                'weather_farm_observed_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_snapshots');
    }
};