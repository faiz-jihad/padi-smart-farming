<?php

namespace Database\Seeders;

use App\Models\AgricultureKnowledge;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DiseaseRecommendationSeeder extends Seeder
{
    /**
     * Seed comprehensive disease master knowledge and agronomy recommendations
     * for all 8 major Indonesian rice diseases and pests.
     */
    public function run(): void
    {
        $diseases = [
            [
                'category'         => 'hama_penyakit',
                'title'            => 'Penyakit Blas Padi (Pyricularia oryzae) — Diagnosis & Pengendalian',
                'summary'          => 'Panduan terpadu mengatasi Blas Daun dan Blas Leher (Patah Leher) dengan fungisida berimbang dan pengelolaan nitrogen yang tepat.',
                'tags'             => ['blas', 'pyricularia', 'jamur', 'patah leher', 'fungisida'],
                'is_featured'      => true,
                'content_markdown' => "### Identifikasi & Penanganan Penyakit Blas Padi (Pyricularia oryzae)

Penyakit Blas merupakan salah satu penyakit paling merusak pada tanaman padi sawah, terutama di musim hujan dengan kelembaban di atas 90%.

#### 1. Gejala Lapangan:
- **Blas Daun**: Bercak berbentuk belah ketupat dengan ujung meruncing, bagian tengah berwarna abu-abu/putih dengan tepi cokelat kemerahan.
- **Blas Leher (Patah Leher)**: Busuk kehitaman pada leher malai yang menyebabkan malai patah dan bulir padi hampa total.

#### 2. Tindakan Pengendalian Kimia:
- Aplikasi fungisida berbahan aktif **Trisiklazol (Tricyclazole 75 WP)** dosis 1-1.5 g/liter air saat fase anakan dan primordia.
- Atau gunakan **Azoksistrobin + Difenokonazol** dosis 1 ml/liter untuk perlindungan translaminar spektrum luas.

#### 3. Tindakan Pengendalian Hayati & Kultur Teknis:
- Hindari pemupukan Urea (Nitrogen) berlebih saat musim hujan.
- Terapkan perendaman benih dengan agens hayati *Paenibacillus polymyxa* atau *Pseudomonas fluorescens*.
- Jaga jarak tanam Jajar Legowo 2:1 agar sirkulasi udara dan intensitas cahaya matahari optimal.",
            ],
            [
                'category'         => 'hama_penyakit',
                'title'            => 'Hawar Daun Bakteri / Kresek (Xanthomonas oryzae pv. oryzae)',
                'summary'          => 'Strategi memutus rantai serangan bakteri kresek daun padi dengan bakterisida sistemik dan varietas tahan seperti Inpari 32.',
                'tags'             => ['kresek', 'hawar daun bakteri', 'xanthomonas', 'bakterisida', 'inpari 32'],
                'is_featured'      => true,
                'content_markdown' => "### Identifikasi & Pengendalian Hawar Daun Bakteri (Kresek)

Penyakit kresek disebabkan oleh bakteri *Xanthomonas oryzae* yang masuk melalui stomata daun atau luka gesekan antar daun.

#### 1. Gejala Utama:
- Timbul bercak basah bergaris kuning pada pinggir daun yang merambat ke pelepah.
- Daun mengering bergelombang menyerupai luka terbakar abu-abu keputihan dari ujung ke pangkal.

#### 2. Tindakan Pengendalian:
- Gunakan bakterisida tembaga atau antibiotik pertanian berbahan aktif **Kasugamisin (Kasugamycin 20 g/l)** atau **Oksitetrasiklin**.
- Lakukan penyemprotan pagi hari setelah embun kering agar bakteri tidak menyebar melalui percikan air.
- Terapkan irigasi berselang (keringkan sawah 2-3 hari) untuk mengurangi kelembaban mikro.",
            ],
            [
                'category'         => 'hama_penyakit',
                'title'            => 'Bercak Coklat Daun (Bipolaris oryzae / Helminthosporium)',
                'summary'          => 'Pengendalian bercak coklat daun akibat defisiensi hara kalium dan serangan patogen jamur Bipolaris.',
                'tags'             => ['bercak coklat', 'bipolaris', 'kalium', 'kcl', 'fungisida'],
                'is_featured'      => false,
                'content_markdown' => "### Identifikasi & Penanganan Penyakit Bercak Coklat Daun

Penyakit bercak coklat sering menjadi indikator tanah kekurangan unsur hara kalium (K), silika (Si), atau tanah masam.

#### 1. Gejala:
- Bercak bulat hingga oval kecil berwarna cokelat tua merata di seluruh permukaan helai daun.
- Daun yang terserang parah akan menguning dan layu sebelum waktunya.

#### 2. Tindakan Pengendalian:
- Berikan pupuk Kalium (**KCl**) 50-75 kg/ha dan pupuk silika organik.
- Semprot fungisida berbahan aktif **Mankozeb 80%** atau **Karbendazim** saat gejala awal mulai tampak.",
            ],
            [
                'category'         => 'hama_penyakit',
                'title'            => 'Virus Tungro Padi & Vektor Wereng Hijau (Nephotettix virescens)',
                'summary'          => 'Pencegahan kerdil tanaman dan daun kuning oranye akibat infeksi virus Tungro yang ditularkan oleh wereng hijau.',
                'tags'             => ['tungro', 'wereng hijau', 'kerdil', 'virus padi'],
                'is_featured'      => true,
                'content_markdown' => "### Penanganan Penyakit Tungro Padi

Tungro disebabkan oleh kombinasi dua virus: *Rice Tungro Bacilliform Virus (RTBV)* dan *Rice Tungro Spherical Virus (RTSV)* melalui vektor wereng daun hijau.

#### 1. Gejala:
- Tanaman kerdil nyata dengan anakan yang sangat sedikit.
- Daun berubah warna menjadi kuning oranye dimulai dari daun yang lebih tua.

#### 2. Tindakan Pengendalian:
- Basmi vektor penular (wereng hijau) menggunakan insektisida berbahan aktif **Tiametoksam** atau **Imidakloprid**.
- Cabut dan bakar tanaman yang terinfeksi agar tidak menjadi sumber inokulum.
- Tanam serempak dalam satu hamparan 50-100 hektare.",
            ],
            [
                'category'         => 'hama_penyakit',
                'title'            => 'Hawar Pelepah Daun (Rhizoctonia solani Kühn)',
                'summary'          => 'Panduan membasmi jamur hawar pelepah padi pada pertanaman rapat dan dosis pupuk N tinggi.',
                'tags'             => ['hawar pelepah', 'rhizoctonia', 'sklerosia', 'fungisida validamisin'],
                'is_featured'      => false,
                'content_markdown' => "### Pengendalian Hawar Pelepah (Rhizoctonia solani)

Penyakit ini menyerang pelepah daun dekat permukaan air saat tanaman mulai rimbun (fase anakan maksimal hingga bunting).

#### 1. Gejala:
- Bercak oval abu-abu kehijauan bertepi cokelat pada pelepah bawah.
- Terdapat butiran sklerosia kecil berwarna putih lalu berubah cokelat.

#### 2. Tindakan Pengendalian:
- Semprot fungisida berbahan aktif **Validamisin** atau **Heksakonazol** tepat di pangkal rumpun padi.
- Keringkan air sawah selama beberapa hari.",
            ],
            [
                'category'         => 'hama_penyakit',
                'title'            => 'Busuk Batang Padi (Sclerotium oryzae Catt.)',
                'summary'          => 'Identifikasi pembusukan pangkal batang padi penyebab tanaman rebah menjelang panen.',
                'tags'             => ['busuk batang', 'sclerotium', 'rebah', 'drainase'],
                'is_featured'      => false,
                'content_markdown' => "### Pengendalian Busuk Batang Padi

Jamur *Sclerotium oryzae* hidup di sisa-sisa jerami padi dan menyerang batang bagian dalam tanaman.

#### 1. Gejala:
- Bercak hitam keabu-abuan membusuk pada pelepah pangkal batang dekat air.
- Rongga batang berisi miselium putih berbulu dan tanaman mudah rebah saat terisi bulir.

#### 2. Tindakan:
- Bersihkan sisa jerami pasca panen dan olah tanah secara sempurna.
- Keringkan petak sawah secara berkala (pengairan intermittent).",
            ],
            [
                'category'         => 'hama_penyakit',
                'title'            => 'Wereng Batang Coklat (Nilaparvata lugens) & Hopperburn',
                'summary'          => 'Pengendalian Hama Terpadu untuk ledakan wereng coklat di pangkal batang rumpun padi.',
                'tags'             => ['wereng coklat', 'wbc', 'hopperburn', 'insektisida pymetrozine'],
                'is_featured'      => true,
                'content_markdown' => "### Manajemen Pengendalian Wereng Batang Coklat (WBC)

Wereng coklat mengisap cairan tanaman padi dari pangkal batang dan dapat menyebabkan kekeringan mendadak (*hopperburn*).

#### 1. Tindakan Pengendalian PHT:
- Keringkan sawah secara total selama 3-5 hari agar lingkungan pangkal rumpun tidak lembab.
- Gunakan insektisida khusus wereng berbahan aktif **Pimetrozin (Pymetrozine 50 WG)** atau **Buprofezin 25 WP** yang menjaga musuh alami (laba-laba & kumbang koksi).
- Hindari insektisida piretroid sintetik yang memicu resurgensi wereng.",
            ],
            [
                'category'         => 'hama_penyakit',
                'title'            => 'Penggerek Batang Padi / Sundep & Beluk (Scirpophaga incertulas)',
                'summary'          => 'Penanganan ulat penggerek batang kuning fase vegetatif (sundep) dan fase generatif (beluk).',
                'tags'             => ['penggerek batang', 'sundep', 'beluk', 'klorantraniliprol'],
                'is_featured'      => true,
                'content_markdown' => "### Penanganan Sundep & Beluk (Penggerek Batang)

Larva penggerek batang masuk ke dalam batang padi dan memotong jaringan pembuluh hara.

#### 1. Gejala:
- **Sundep (Vegetatif)**: Pucuk daun muda layu, mengering dan mudah dicabut.
- **Beluk (Generatif)**: Malai padi tegak keputihan dan hampa karena pangkal malai terputus.

#### 2. Tindakan Pengendalian:
- Kumpulkan dan musnahkan kelompok telur berbulu halus di daun bibit sebelum tanam.
- Aplikasi insektisida sistemik berbahan aktif **Klorantraniliprol (Chlorantraniliprole 50 SC)** atau **Dimehipo** pada saat penerbangan ngengat puncak.",
            ],
        ];

        foreach ($diseases as $item) {
            AgricultureKnowledge::updateOrCreate(
                ['title' => $item['title']],
                [
                    'category'         => $item['category'],
                    'slug'             => Str::slug($item['title']),
                    'summary'          => $item['summary'],
                    'content_markdown' => $item['content_markdown'],
                    'tags'             => $item['tags'],
                    'is_featured'      => $item['is_featured'],
                    'published_at'     => now(),
                ]
            );
        }
    }
}
