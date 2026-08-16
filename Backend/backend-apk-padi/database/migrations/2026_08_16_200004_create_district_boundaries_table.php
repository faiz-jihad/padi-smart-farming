<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('district_boundaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->unique()->constrained('districts')->cascadeOnDelete();
            // GeoJSON Polygon/MultiPolygon disimpan sebagai JSON string
            $table->longText('geometry')->comment('GeoJSON geometry object');
            // Bounding box: [minLng, minLat, maxLng, maxLat]
            $table->json('bbox')->nullable()->comment('Bounding box array [minLng, minLat, maxLng, maxLat]');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('district_boundaries');
    }
};
