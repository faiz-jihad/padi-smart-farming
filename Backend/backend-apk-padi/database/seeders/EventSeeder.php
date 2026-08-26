<?php

namespace Database\Seeders;

use App\Models\AgricultureEvent;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        $events = [
            [
                'title' => 'Workshop Pemupukan Berimbang & Uji Cepat Hara Tanah',
                'description' => 'Pelatihan langsung bagi petani untuk menguji kadar NPK dan pH tanah sawah secara cepat dengan alat uji tanah sawah (PUTS) serta panduan pemupukan presisi berbasis aplikasi P.A.D.I. Peserta akan mendapatkan sampel pupuk organik dan sertifikat keikutsertaan.',
                'category' => 'workshop',
                'event_date' => now()->addDays(3)->toDateString(),
                'event_time' => '08:30 - 12:30 WIB',
                'location_name' => 'Balai Penyuluhan Pertanian (BPP) Karangampel',
                'location_address' => 'Jl. Raya Karangampel No. 45, Karangampel, Indramayu',
                'is_online' => false,
                'organizer' => 'Dinas Ketahanan Pangan & Pertanian Indramayu',
                'speaker' => 'Dr. Ir. Hendro Wibowo (Pakar Agronomi IPB)',
                'quota' => 60,
                'registered_count' => 42,
                'price_type' => 'free',
                'asset_image' => 'assets/images/onboarding_1.jpeg',
                'contact_person' => '0812-3456-7890 (Pak Sugeng - Koordinator BPP)',
                'status' => 'upcoming',
                'created_by' => $admin?->id,
            ],
            [
                'title' => 'Sekolah Lapang: Deteksi Dini Penyakit & Pengendalian Wereng',
                'description' => 'Praktik lapangan identifikasi gejala serangan wereng batang coklat (WBC) dan blas daun padi menggunakan kamera AI. Diskusi strategi pengendalian hayati terpadu tanpa merusak musuh alami tanaman.',
                'category' => 'field_day',
                'event_date' => now()->addDays(7)->toDateString(),
                'event_time' => '07:30 - 11:30 WIB',
                'location_name' => 'Hamparan Sawah Gapoktan Sri Rejeki',
                'location_address' => 'Desa Jatibarang Baru, Kec. Jatibarang, Indramayu',
                'is_online' => false,
                'organizer' => 'POPT Balai Proteksi Tanaman Pangan & Hortikultura',
                'speaker' => 'H. Suwandi, S.P. (POPT Ahli Muda)',
                'quota' => 50,
                'registered_count' => 35,
                'price_type' => 'free',
                'asset_image' => 'assets/images/onboarding_2.jpeg',
                'contact_person' => '0857-9876-5432 (Ibu Ratna - PPL Wilayah)',
                'status' => 'upcoming',
                'created_by' => $admin?->id,
            ],
            [
                'title' => 'Bazar Tani & Temu Usaha Kemitraan Pembeli Gabah Panen Raya',
                'description' => 'Pertemuan langsung antara gabungan kelompok tani (Gapoktan) produsen gabah kualitas premium dengan mitra penggilingan modern, Bulog, dan pembeli off-taker. Dapatkan kepastian harga beli di atas HPP resmi.',
                'category' => 'bazaar',
                'event_date' => now()->addDays(12)->toDateString(),
                'event_time' => '09:00 - 15:00 WIB',
                'location_name' => 'Pasar Induk Beras & Gedung Pertemuan Pertanian',
                'location_address' => 'Kawasan Agribisnis Terpadu Sindang, Indramayu',
                'is_online' => false,
                'organizer' => 'P.A.D.I. Marketplace & Koperasi Tani Makmur',
                'speaker' => 'Direktur Pengadaan Pangan Bulog & Asosiasi Penggilingan Padi',
                'quota' => 120,
                'registered_count' => 88,
                'price_type' => 'free',
                'asset_image' => 'assets/images/onboarding_3.jpeg',
                'contact_person' => '0813-8888-9999 (Sekretariat Bazar Tani P.A.D.I.)',
                'status' => 'upcoming',
                'created_by' => $admin?->id,
            ],
            [
                'title' => 'Musyawarah Petani & Sosialisasi Jadwal Gilir Air Musim Gadu',
                'description' => 'Koordinasi pembagian debit saluran irigasi primer dan sekunder rentang daerah irigasi Jatiluhur - Rentang. Pembagian jadwal rotasi pompa air sumur pantek dan pemeliharaan pintu air.',
                'category' => 'irrigation',
                'event_date' => now()->addDays(18)->toDateString(),
                'event_time' => '08:00 - 11:00 WIB',
                'location_name' => 'Kantor Pengamat Pengairan Wilayah Sindang',
                'location_address' => 'Jl. Irigasi No. 12, Sindang, Indramayu',
                'is_online' => false,
                'organizer' => 'Perkumpulan Petani Pemakai Air (P3A) Mitra Cai',
                'speaker' => 'Ir. Bambang Sutejo (Balai Besar Wilayah Sungai Cimanuk Cisanggarung)',
                'quota' => 80,
                'registered_count' => 54,
                'price_type' => 'free',
                'asset_image' => 'assets/images/onboarding_1.jpeg',
                'contact_person' => '0821-2233-4455 (Ketua Induk P3A)',
                'status' => 'upcoming',
                'created_by' => $admin?->id,
            ],
        ];

        foreach ($events as $data) {
            AgricultureEvent::updateOrCreate(
                ['title' => $data['title']],
                $data
            );
        }
    }
}
