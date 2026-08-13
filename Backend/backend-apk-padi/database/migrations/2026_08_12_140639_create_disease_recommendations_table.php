<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disease_recommendations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('scan_id')
                ->constrained('disease_scans')
                ->cascadeOnDelete();

            $table->string('source', 30);

            $table->string('llm_model', 100)->nullable();

            $table->text('explanation')->nullable();

            $table->text('action')->nullable();

            $table->string('safety_note', 500)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disease_recommendations');
    }
};