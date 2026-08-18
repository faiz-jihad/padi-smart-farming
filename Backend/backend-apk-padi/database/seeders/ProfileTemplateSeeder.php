<?php

namespace Database\Seeders;

use App\Models\ProfileTemplate;
use Illuminate\Database\Seeder;

class ProfileTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name'        => 'Harvest Prestige',
                'code'        => 'harvest-prestige',
                'description' => 'Template premium editorial luxury agriculture. Cocok untuk petani padi dengan lahan luas, produk kualitas tinggi, dan ingin tampil profesional di hadapan pembeli premium.',
                'is_premium'  => false,
                'status'      => 'active',
                'sort_order'  => 1,
            ],
            [
                'name'        => 'Agri Modern',
                'code'        => 'agri-modern',
                'description' => 'Template modern agritech dengan grid bersih dan tampilan data-driven. Cocok untuk petani muda, agripreneur, dan kelompok tani yang ingin tampil progresif dan tech-forward.',
                'is_premium'  => false,
                'status'      => 'active',
                'sort_order'  => 2,
            ],
            [
                'name'        => 'Marketplace Pro',
                'code'        => 'marketplace-pro',
                'description' => 'Template yang dioptimalkan untuk konversi penjualan. Menampilkan produk dan stok panen di halaman utama dengan CTA yang jelas. Cocok untuk petani yang aktif berjualan langsung.',
                'is_premium'  => false,
                'status'      => 'active',
                'sort_order'  => 3,
            ],
        ];

        foreach ($templates as $template) {
            ProfileTemplate::firstOrCreate(
                ['code' => $template['code']],
                $template
            );
        }
    }
}
