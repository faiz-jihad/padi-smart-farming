<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100);

            $table->string('phone', 20)->unique();

            $table->string('password');

            $table->enum('role', [
                'farmer',
                'ppl',
                'partner',
                'admin',
            ])->index();

            $table->enum('status', [
                'active',
                'inactive',
                'suspended',
            ])->default('active')->index();

            $table->enum('verification_status', [
                'pending',
                'verified',
                'rejected',
            ])->default('pending')->index();

            $table->timestamps();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};