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
        Schema::create('irrigation_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Seed initial default irrigation types
        $defaultTypes = [
            [
                'name' => 'Irigasi Teknis',
                'code' => 'teknis',
                'description' => 'Saluran primer, sekunder, dan tersier teratur dengan pintu air pengukur debit.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Irigasi Setengah Teknis',
                'code' => 'setengah_teknis',
                'description' => 'Saluran primer dan sekunder permanen, pengaturan pembagian air semi-terkontrol.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sawah Tadah Hujan',
                'code' => 'hujan',
                'description' => 'Pengairan bergantung penuh pada curah hujan musiman tanpa jaringan irigasi permanen.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Rawa / Pasang Surut',
                'code' => 'swamp',
                'description' => 'Pemanfaatan pasang surut air muara atau lahan rawa dengan saluran tata air khusus.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Irigasi Pompa',
                'code' => 'pompa',
                'description' => 'Sistem pengairan mengambil air tanah dalam atau sungai menggunakan mesin pompa.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Lainnya',
                'code' => 'lainnya',
                'description' => 'Sistem pengairan alternatif, sumur dangkal, atau kearifan lokal setempat.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('irrigation_types')->insert($defaultTypes);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('irrigation_types');
    }
};
