<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('farms', function (Blueprint $table) {
            // Tambah FK region — semuanya nullable agar backward compatible
            $table->foreignId('province_id')->nullable()->after('farmer_user_id')
                ->constrained('provinces')->nullOnDelete();
            $table->foreignId('regency_id')->nullable()->after('province_id')
                ->constrained('regencies')->nullOnDelete();
            $table->foreignId('district_id')->nullable()->after('regency_id')
                ->constrained('districts')->nullOnDelete();
            $table->foreignId('village_id')->nullable()->after('district_id')
                ->constrained('villages')->nullOnDelete();

            // Tambahan metadata lahan
            $table->string('soil_type', 50)->nullable()->after('irrigation_notes');
            $table->enum('status', ['active', 'inactive', 'fallow'])->default('active')->after('soil_type');
        });
    }

    public function down(): void
    {
        Schema::table('farms', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropForeign(['regency_id']);
            $table->dropForeign(['district_id']);
            $table->dropForeign(['village_id']);
            $table->dropColumn(['province_id', 'regency_id', 'district_id', 'village_id', 'soil_type', 'status']);
        });
    }
};
