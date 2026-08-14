<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('scan_id')
                ->constrained('disease_scans')
                ->cascadeOnDelete();

            $table->foreignId('farmer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->decimal('latitude', 10, 7);

            $table->decimal('longitude', 10, 7);

            $table->decimal('radius_km', 6, 2);

            $table->boolean('consent_given')->default(false);

            $table->string('status', 30)->default('pending');

            $table->timestamp('reported_at');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_reports');
    }
};