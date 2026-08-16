<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regency_id')->constrained('regencies')->cascadeOnDelete();
            $table->string('code', 10)->unique()->comment('Kode BPS/Kemendagri');
            $table->string('name', 100);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();

            $table->index(['regency_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
