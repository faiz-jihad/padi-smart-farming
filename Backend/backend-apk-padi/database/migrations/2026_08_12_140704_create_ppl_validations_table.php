<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppl_validations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('scan_id')
                ->constrained('disease_scans')
                ->cascadeOnDelete();

            $table->foreignId('ppl_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('status', 30);

            $table->text('notes')->nullable();

            $table->timestamp('validated_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppl_validations');
    }
};