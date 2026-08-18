<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_templates', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100);

            // Unique code — used to resolve Blade template file
            $table->string('code', 50)->unique();

            $table->text('description')->nullable();

            $table->string('thumbnail_path', 500)->nullable();

            $table->string('preview_image_path', 500)->nullable();

            $table->boolean('is_premium')->default(false);

            $table->enum('status', ['active', 'inactive'])->default('active')->index();

            $table->smallInteger('sort_order')->unsigned()->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_templates');
    }
};
