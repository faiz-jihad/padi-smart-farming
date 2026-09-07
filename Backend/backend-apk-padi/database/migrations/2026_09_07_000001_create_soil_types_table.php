<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('soil_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Seed initial default soil types
        $defaultTypes = [
            [
                'name' => 'Lempung Berpasir / Loam',
                'code' => 'loam',
                'description' => 'Ideal untuk padi sawah, retensi air dan sirkulasi aerasi seimbang.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Aluvial',
                'code' => 'alluvial',
                'description' => 'Endapan sungai dan dataran rendah dengan kesuburan alami tinggi.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Liat / Clay',
                'code' => 'clay',
                'description' => 'Kapasitas retensi air tinggi, cocok untuk sistem penggenangan padi.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pasir Berlempung / Sandy Loam',
                'code' => 'sandy_loam',
                'description' => 'Tekstur berpasir yang memerlukan manajemen air berselang lebih intensif.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Latosol / Merah Kuning',
                'code' => 'latosol',
                'description' => 'Tanah dengan tingkat pelapukan lanjut, memerlukan pengayaan bahan organik.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gambut / Peat',
                'code' => 'peat',
                'description' => 'Lahan rawa gambut dengan bahan organik tinggi dan pH cenderung masam.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('soil_types')->insert($defaultTypes);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soil_types');
    }
};
