<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'GKP Panen',
                'slug' => 'gkp-panen',
                'icon' => 'grass',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'GKG Giling',
                'slug' => 'gkg-giling',
                'icon' => 'grain',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Beras Premium',
                'slug' => 'beras-premium',
                'icon' => 'rice_bowl',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Benih Bersertifikat',
                'slug' => 'benih-bersertifikat',
                'icon' => 'spa',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                [
                    'slug' => $category['slug'],
                ],
                $category,
            );
        }
    }
}
