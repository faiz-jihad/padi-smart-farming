<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planting_calendars', function (Blueprint $table) {
            $table->id();

            // Referensi wilayah — semua nullable untuk mendukung level hierarki berbeda
            $table->foreignId('province_id')->nullable()->constrained('provinces')->nullOnDelete();
            $table->foreignId('regency_id')->nullable()->constrained('regencies')->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('village_id')->nullable()->constrained('villages')->nullOnDelete();

            $table->enum('season', ['rainy', 'dry', 'transition'])->comment('Musim tanam');
            $table->smallInteger('year')->unsigned();

            $table->date('planting_start')->comment('Awal anjuran tanam');
            $table->date('planting_end')->comment('Akhir anjuran tanam');

            $table->string('planting_pattern', 100)->nullable()->comment('Contoh: Padi-Bera-Padi');
            $table->string('rice_variety', 100)->nullable()->comment('Varietas yang dianjurkan');
            $table->decimal('recommended_area', 10, 2)->nullable()->comment('Luas target dalam ha');

            $table->enum('status', ['draft', 'active', 'inactive'])->default('draft');
            $table->string('source', 200)->nullable()->comment('Sumber data, mis: BMKG, Dinas Pertanian');
            $table->text('notes')->nullable();

            $table->timestamps();

            // Index untuk query berdasarkan wilayah + musim + tahun
            $table->index(['district_id', 'season', 'year']);
            $table->index(['regency_id', 'season', 'year']);
            $table->index(['village_id', 'season', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planting_calendars');
    }
};
