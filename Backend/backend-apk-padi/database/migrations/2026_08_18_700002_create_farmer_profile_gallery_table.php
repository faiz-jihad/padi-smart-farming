<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farmer_profile_gallery', function (Blueprint $table) {
            $table->id();

            $table->foreignId('farmer_public_profile_id')
                ->constrained('farmer_public_profiles')
                ->cascadeOnDelete();

            $table->string('image_path', 500);

            $table->string('caption', 255)->nullable();

            $table->smallInteger('sort_order')->unsigned()->default(0);

            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index(['farmer_public_profile_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farmer_profile_gallery');
    }
};
