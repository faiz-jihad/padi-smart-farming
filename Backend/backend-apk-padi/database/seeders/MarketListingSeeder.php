<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\CropSeason;
use App\Models\District;
use App\Models\Farm;
use App\Models\Harvest;
use App\Models\MarketListing;
use App\Models\RiceVariety;
use App\Models\User;
use App\Models\Village;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MarketListingSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create or get Farmers
        $farmer1 = User::firstOrCreate(
            ['email' => 'pak.rohim@padi.test'],
            [
                'name' => 'H. Rohim (Gapoktan Sri Rejeki)',
                'phone' => '081234567801',
                'password' => Hash::make('password'),
                'role' => UserRole::Farmer->value,
                'status' => UserStatus::Active->value,
            ]
        );
        $farmer1->assignRole(UserRole::Farmer->value);

        $farmer2 = User::firstOrCreate(
            ['email' => 'siti.aminah@padi.test'],
            [
                'name' => 'Ibu Siti Aminah (Koperasi Tani Makmur)',
                'phone' => '081234567802',
                'password' => Hash::make('password'),
                'role' => UserRole::Farmer->value,
                'status' => UserStatus::Active->value,
            ]
        );
        $farmer2->assignRole(UserRole::Farmer->value);

        $farmer3 = User::firstOrCreate(
            ['email' => 'taryana@padi.test'],
            [
                'name' => 'Pak Taryana (Kelompok Tani Sindang Jaya)',
                'phone' => '081234567803',
                'password' => Hash::make('password'),
                'role' => UserRole::Farmer->value,
                'status' => UserStatus::Active->value,
            ]
        );
        $farmer3->assignRole(UserRole::Farmer->value);

        // Get or Create Rice Varieties
        $inpari = RiceVariety::firstOrCreate(
            ['name' => 'Inpari 32'],
            ['description' => 'Tahan terhadap penyakit hawar daun bakteri dan blas, tekstur pulen sedang.', 'duration_days' => 115]
        );
        $ciherang = RiceVariety::firstOrCreate(
            ['name' => 'Ciherang Super'],
            ['description' => 'Varietas unggulan dengan produktivitas tinggi dan beras putih bening.', 'duration_days' => 110]
        );
        $pandanWangi = RiceVariety::firstOrCreate(
            ['name' => 'Pandan Wangi Organik'],
            ['description' => 'Aroma pandan alami yang khas, rasa sangat pulen dan bernilai ekonomis tinggi.', 'duration_days' => 125]
        );
        $mekongga = RiceVariety::firstOrCreate(
            ['name' => 'Mekongga'],
            ['description' => 'Tahan wereng coklat biotipe 2 dan 3, sangat cocok untuk lahan irigasi teknis.', 'duration_days' => 118]
        );

        $district = District::first();
        $village = Village::first();

        // 2. Create Farms
        $farm1 = Farm::firstOrCreate(
            ['name' => 'Hamparan Sawah Blok Kedokan', 'farmer_user_id' => $farmer1->id],
            [
                'area_ha' => 2.5,
                'latitude' => -6.3421,
                'longitude' => 108.3341,
                'irrigation_type' => 'irrigated',
                'district_id' => $district?->id,
                'village_id' => $village?->id,
                'status' => 'active',
            ]
        );

        $farm2 = Farm::firstOrCreate(
            ['name' => 'Sawah Sindang Jaya Blok A', 'farmer_user_id' => $farmer2->id],
            [
                'area_ha' => 3.2,
                'latitude' => -6.3512,
                'longitude' => 108.3188,
                'irrigation_type' => 'irrigated',
                'district_id' => $district?->id,
                'village_id' => $village?->id,
                'status' => 'active',
            ]
        );

        $farm3 = Farm::firstOrCreate(
            ['name' => 'Sawah Petak Timur Jatibarang', 'farmer_user_id' => $farmer3->id],
            [
                'area_ha' => 1.8,
                'latitude' => -6.3689,
                'longitude' => 108.3054,
                'irrigation_type' => 'irrigated',
                'district_id' => $district?->id,
                'village_id' => $village?->id,
                'status' => 'active',
            ]
        );

        // 3. Create Crop Seasons
        $season1 = CropSeason::firstOrCreate(
            ['farm_id' => $farm1->id, 'variety_id' => $inpari->id],
            [
                'planting_date' => now()->subDays(110)->toDateString(),
                'estimated_harvest_date' => now()->subDays(5)->toDateString(),
                'status' => 'completed',
            ]
        );

        $season2 = CropSeason::firstOrCreate(
            ['farm_id' => $farm2->id, 'variety_id' => $ciherang->id],
            [
                'planting_date' => now()->subDays(105)->toDateString(),
                'estimated_harvest_date' => now()->subDays(2)->toDateString(),
                'status' => 'completed',
            ]
        );

        $season3 = CropSeason::firstOrCreate(
            ['farm_id' => $farm3->id, 'variety_id' => $pandanWangi->id],
            [
                'planting_date' => now()->subDays(120)->toDateString(),
                'estimated_harvest_date' => now()->toDateString(),
                'status' => 'completed',
            ]
        );

        // 4. Create Harvests
        $harvest1 = Harvest::firstOrCreate(
            ['crop_season_id' => $season1->id],
            [
                'harvest_date' => now()->subDays(5)->toDateString(),
                'quantity' => 12500,
                'unit' => 'kg',
                'quality_grade' => 'A',
                'moisture_percent' => 21.5,
            ]
        );

        $harvest2 = Harvest::firstOrCreate(
            ['crop_season_id' => $season2->id],
            [
                'harvest_date' => now()->subDays(2)->toDateString(),
                'quantity' => 16000,
                'unit' => 'kg',
                'quality_grade' => 'A',
                'moisture_percent' => 14.0,
            ]
        );

        $harvest3 = Harvest::firstOrCreate(
            ['crop_season_id' => $season3->id],
            [
                'harvest_date' => now()->toDateString(),
                'quantity' => 8500,
                'unit' => 'kg',
                'quality_grade' => 'A',
                'moisture_percent' => 13.5,
            ]
        );

        // 5. Create Realistic Market Listings
        $listings = [
            [
                'farmer_id' => $farmer1->id,
                'farm_id' => $farm1->id,
                'crop_season_id' => $season1->id,
                'harvest_id' => $harvest1->id,
                'commodity' => 'Gabah Kering Panen (GKP) Inpari 32',
                'quantity' => 5000,
                'unit' => 'kg',
                'price_per_unit' => 6850,
                'description' => 'Gabah Kering Panen (GKP) segar baru dipotong combine harvester. Varietas Inpari 32, bulir bernas dan padat, kadar air 21.5%, hampa kotoran di bawah 7%. Lokasi tepi jalan raya mudah diakses truk tronton/engkel.',
                'sales_link' => 'https://wa.me/6281234567801?text=Halo%20Pak%20Rohim,%20saya%20tertarik%20dengan%20listing%20GKP%20Inpari%2032%20di%20PADI',
                'status' => 'published',
                'published_at' => now()->subHours(4),
                'expires_at' => now()->addDays(30),
            ],
            [
                'farmer_id' => $farmer2->id,
                'farm_id' => $farm2->id,
                'crop_season_id' => $season2->id,
                'harvest_id' => $harvest2->id,
                'commodity' => 'Gabah Kering Giling (GKG) Ciherang Super',
                'quantity' => 7500,
                'unit' => 'kg',
                'price_per_unit' => 7600,
                'description' => 'Gabah Kering Giling (GKG) kualitas super siap masuk Rice Milling Unit (RMU). Kadar air stabil 14%, rendemen beras diprediksi di atas 66%. Bersih dari jerami dan batu, kemasan karung rapi.',
                'sales_link' => 'https://wa.me/6281234567802?text=Halo%20Ibu%20Siti,%20saya%20berminat%20dengan%20GKG%20Ciherang%20Super',
                'status' => 'published',
                'published_at' => now()->subHours(12),
                'expires_at' => now()->addDays(30),
            ],
            [
                'farmer_id' => $farmer3->id,
                'farm_id' => $farm3->id,
                'crop_season_id' => $season3->id,
                'harvest_id' => $harvest3->id,
                'commodity' => 'Beras Premium Pandan Wangi Organik',
                'quantity' => 2500,
                'unit' => 'kg',
                'price_per_unit' => 15500,
                'description' => 'Beras Organik Pandan Wangi asli Indramayu. Wangi alami, tanpa pemutih, tanpa pengawet, dan tanpa pewangi buatan. Tersedia dalam kemasan vakum 5kg, 10kg, dan sak 25kg berlabel sertifikasi P.A.D.I. Grade A.',
                'sales_link' => 'https://wa.me/6281234567803?text=Halo%20Pak%20Taryana,%20saya%20ingin%20pesan%20Beras%20Pandan%20Wangi%20Organik',
                'status' => 'published',
                'published_at' => now()->subDays(1),
                'expires_at' => now()->addDays(30),
            ],
            [
                'farmer_id' => $farmer1->id,
                'farm_id' => $farm1->id,
                'crop_season_id' => $season1->id,
                'harvest_id' => null,
                'commodity' => 'Gabah Kering Panen (GKP) Mekongga Segar',
                'quantity' => 4200,
                'unit' => 'kg',
                'price_per_unit' => 6900,
                'description' => 'Hasil panen sawah irigasi teknis Karangampel. Varietas Mekongga, bulir panjang ramping, warna kuning keemasan bersih. Penimbangan langsung di lokasi menggunakan timbangan digital terkalibrasi.',
                'sales_link' => 'https://wa.me/6281234567801?text=Halo,%20saya%20tertarik%20GKP%20Mekongga%20di%20PADI',
                'status' => 'published',
                'published_at' => now()->subDays(2),
                'expires_at' => now()->addDays(30),
            ],
            [
                'farmer_id' => $farmer2->id,
                'farm_id' => $farm2->id,
                'crop_season_id' => $season2->id,
                'harvest_id' => null,
                'commodity' => 'Benih Padi Bersertifikat Inpari 32 (Label Biru)',
                'quantity' => 1500,
                'unit' => 'kg',
                'price_per_unit' => 18000,
                'description' => 'Benih padi sebar bersertifikat resmi BPSBTPH Jawa Barat (Label Biru). Daya tumbuh/perkecambahan >95%, kemurnian varietas >99%, kadar air 12%. Dilengkapi perlakuan fungisida hayati pencegah penyakit tular benih.',
                'sales_link' => 'https://wa.me/6281234567802?text=Halo%20Ibu%20Siti,%20mau%20order%20Benih%20Inpari%2032',
                'status' => 'published',
                'published_at' => now()->subDays(3),
                'expires_at' => now()->addDays(30),
            ],
            [
                'farmer_id' => $farmer3->id,
                'farm_id' => $farm3->id,
                'crop_season_id' => $season3->id,
                'harvest_id' => null,
                'commodity' => 'Beras Medium IR 64 Pulen',
                'quantity' => 6000,
                'unit' => 'kg',
                'price_per_unit' => 13800,
                'description' => 'Beras putih pulen IR 64 cocok untuk konsumsi harian keluarga, rumah makan, dan katering. Derajat sosoh 95%, butir patah (broken) maksimal 15%. Kemasan karung 50kg siap kirim se-Jabodetabek & Jabar.',
                'sales_link' => 'https://wa.me/6281234567803?text=Halo%20Pak%20Taryana,%20saya%20ingin%20tanya%20Beras%20IR%2064',
                'status' => 'published',
                'published_at' => now()->subDays(4),
                'expires_at' => now()->addDays(30),
            ],
        ];

        foreach ($listings as $data) {
            MarketListing::updateOrCreate(
                [
                    'farmer_id' => $data['farmer_id'],
                    'commodity' => $data['commodity'],
                ],
                $data
            );
        }
    }
}
