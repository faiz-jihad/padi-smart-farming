<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agriculture_knowledges', function (Blueprint $table): void {
            $table->id();
            $table->string('category')->index(); // pemupukan, hama_penyakit, irigasi_sri, sistem_tanam, varietas_padi, pasca_panen
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary');
            $table->longText('content_markdown');
            $table->json('tags')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agriculture_knowledges');
    }
};
