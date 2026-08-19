<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->cascadeOnDelete();
            $table->string('code', 15)->unique()->comment('Kode BPS/Kemendagri');
            $table->string('name', 100);
            $table->enum('type', ['village', 'urban_village'])->default('village');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();

            $table->index(['district_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villages');
    }
};
