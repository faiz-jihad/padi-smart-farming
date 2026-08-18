<?php

namespace App\Services\Agriculture;

use App\Models\AgricultureKnowledge;
use Illuminate\Support\Str;

class AgricultureKnowledgeService
{
    /**
     * Seed 6 comprehensive Indonesian rice farming guides
     */
    public function seedKnowledgeGuides(): int
    {
        $articles = [
            [
                'category' => 'pemupukan',
                'title' => 'Panduan Pemupukan Berimbang Tanaman Padi Sawah (Dosis NPK, Urea, SP-36)',
                'summary' => 'Strategi dosis dan waktu aplikasi pupuk NPK, Urea, dan SP-36 berimbang sesuai fase pertumbuhan padi untuk mencapai hasil maksimal 10+ ton/ha.',
                'tags' => ['pemupukan', 'npk', 'urea', 'pupuk berimbang', 'fase tanam'],
                'is_featured' => true,
                'content_markdown' => "### Panduan Dosis & Waktu Pemupukan Berimbang Padi

Pemupukan berimbang adalah kunci utama pencapaian produktivitas tinggi tanaman padi sawah. Dosis yang direkomendasikan Kementan RI per hektare:

1. **Pemupukan Dasar (Fase Tanam 7-10 HST)**:
   - **Urea**: 50 kg/ha
   - **NPK Phonska (15-15-15)**: 150 kg/ha
   - **SP-36 / SP-18**: 50 kg/ha
   - *Fungsi*: Merangsang pembentukan akar muda dan anakan awal.

2. **Pemupukan Susulan I (Fase Pembentukan Anakan Maksimal 21-25 HST)**:
   - **Urea**: 75 kg/ha
   - **NPK Phonska**: 100 kg/ha
   - *Fungsi*: Memperbanyak anakan produktif per rumpun (target 25-30 anakan/rumpun).

3. **Pemupukan Susulan II (Fase Primordia / Bunting 40-45 HST)**:
   - **Urea**: 50 kg/ha (bila daun agak kekuningan / periksa Bagan Warna Daun - BWD)
   - **KCl**: 50 kg/ha
   - *Fungsi*: Memperpanjang malai padi dan meningkatkan pengisian gabah.

*Tips*: Gunakan Bagan Warna Daun (BWD) skala 4 untuk menentukan kebutuhan Urea secara presisi.",
            ],
            [
                'category' => 'hama_penyakit',
                'title' => 'Strategi Pengendalian Hama & Penyakit Padi Utama (Wereng, Tikus, Blas, Hawar Daun)',
                'summary' => 'Pengendalian Hama Terpadu (PHT) untuk mengatasi Wereng Coklat, Tikus Sawah, Penggerek Batang, penyakit Blas Pyricularia, dan Hawar Daun Bakteri.',
                'tags' => ['hama', 'penyakit', 'wereng', 'tikus', 'blas', 'pht'],
                'is_featured' => true,
                'content_markdown' => "### Panduan Pengendalian Hama Terpadu (PHT) Padi

Serangan HPT dapat menurunkan hasil panen hingga 80% jika tidak ditangani dengan benar:

1. **Wereng Batang Coklat (WBC)**:
   - *Gejala*: Tanaman menguning seperti terbakar (hopperburn).
   - *Pengendalian*: Keringkan air sawah berkelanjutan (intermittent), gunakan bahan aktif buprofezin, pymetrozine, atau imidacloprid.

2. **Tikus Sawah (Rattus argentiventer)**:
   - *Gejala*: Batang padi terpotong patah membentuk sudut 45 derajat.
   - *Pengendalian*: Gropyokan masal sebelum tanam, pasang TBS (Trap Barrier System), manfaatkan burung hantu (Tyto alba).

3. **Penyakit Blas (Pyricularia oryzae)**:
   - *Gejala*: Bercak belah ketupat pada daun dan leher malai patah (patah leher).
   - *Pengendalian*: Kurangi dosis N berlebihan, gunakan fungisida trisiklazol atau azoksistrobin.

4. **Hawar Daun Bakteri (Xanthomonas oryzae / Kresek)**:
   - *Gejala*: Daun mengering dari ujung bergelombang keabu-abuan.
   - *Pengendalian*: Gunakan varietas tahan (Inpari 32), kendalikan air irigasi.",
            ],
            [
                'category' => 'irigasi_sri',
                'title' => 'Teknik Irigasi Berselang (Intermittent Irrigation System / SRI Padi)',
                'summary' => 'Metode pengairan berselang basah-kering untuk menghemat penggunaan air irigasi hingga 30% serta meningkatkan sirkulasi oksigen zona akar.',
                'tags' => ['irigasi', 'intermittent', 'sri', 'hemat air', 'drainase'],
                'is_featured' => true,
                'content_markdown' => "### Prinsip Kerja Irigasi Berselang (Intermittent Irrigation)

Irigasi berselang adalah pengaturan pengairan lahan sawah yang dilakukan secara bergantian antara kondisi tergenang dan kondisi kering macak-macak.

1. **Fase Pengolahan & Tanam (0-10 HST)**:
   - Genangi air 2-3 cm untuk membantu adaptasi bibit baru pindah tanam.

2. **Fase Pembentukan Anakan (11-35 HST)**:
   - Alirkan air hingga ketinggian 3 cm, biarkan air mengering secara alami sampai tanah macak-macak / pecah rambut (kelembaban ~40%), lalu alirkan kembali air 3 cm.

3. **Fase Pembungaan & Pengisian Bulir (45-75 HST)**:
   - Pertahankan genangan air 3-5 cm karena tanaman sangat peka terhadap kekurangan air pada fase bunting dan berbunga.

4. **Fase Pematangan (85-105 HST)**:
   - Keringkan air total 10-14 hari sebelum panen untuk mematangkan bulir secara serentak dan mempermudah alat pemanen (Combine Harvester).",
            ],
            [
                'category' => 'sistem_tanam',
                'title' => 'Sistem Tanam Jajar Legowo 2:1 dan 4:1 untuk Peningkatan Hasil 20-30%',
                'summary' => 'Pola tanam selang-seling dengan ruang kosong untuk menambah populasi tanaman tepi yang menerima sinar matahari maksimal.',
                'tags' => ['jajar legowo', 'sistem tanam', 'populasi', 'produktivitas'],
                'is_featured' => false,
                'content_markdown' => "### Keunggulan Sistem Tanam Jajar Legowo

Jajar Legowo adalah cara tanam padi sawah dengan pola beberapa barisan tanaman yang diselingi satu barisan kosong.

- **Legowo 2:1**: Setiap 2 baris tanaman diselingi 1 baris kosong lebar 40 cm.
- **Legowo 4:1**: Setiap 4 baris tanaman diselingi 1 baris kosong lebar 40 cm.

**Manfaat Utama**:
1. Menambah populasi tanaman hingga 21.3% dibanding sistem tegel konvensional.
2. Memaksimalkan pengaruh tanaman pinggir (border effect) yang menerima fotosintesis lebih tinggi.
3. Memudahkan pemupukan, penyiangan rumput, dan pengendalian hama.
4. Menekan serangan penyakit karena sirkulasi udara di rumpun padi lebih lancar.",
            ],
            [
                'category' => 'varietas_padi',
                'title' => 'Katalog Varietas Unggul Padi Indonesia (Inpari 32, Ciherang, Impari 42, Mekongga)',
                'summary' => 'Karakteristik deskripsi, umur panen, potensi hasil, dan ketahanan HPT dari varietas padi unggul nasional.',
                'tags' => ['varietas', 'inpari 32', 'ciherang', 'inpari 42', 'benih unggul'],
                'is_featured' => false,
                'content_markdown' => "### Katalog Varietas Unggul Padi Sawah

1. **Inpari 32 HDB**:
   - Umur: 115 hari.
   - Potensi Hasil: 10.5 ton/ha (Rata-rata 8.4 ton/ha).
   - Ketahanan: Tahan Hawar Daun Bakteri patotipe III, tahan Blas race 033.

2. **Ciherang**:
   - Umur: 116 hari.
   - Potensi Hasil: 10.0 ton/ha.
   - Ketahanan: Tahan Wereng Coklat biotipe 2 dan 3, rasa nasi pulen disukai pasar.

3. **Inpari 42 Agritan GSR (Green Super Rice)**:
   - Umur: 112 hari.
   - Potensi Hasil: 10.58 ton/ha.
   - Ketahanan: Sangat toleran kekeringan, efisien penggunaan pupuk N.

4. **Mekongga**:
   - Umur: 118 hari.
   - Potensi Hasil: 9.5 ton/ha.
   - Ketahanan: Tahan penyakit Blas dan Wereng Coklat biotipe 2.",
            ],
            [
                'category' => 'pasca_panen',
                'title' => 'Pengelolaan Pasca Panen & Pengeringan Gabah Kering Giling (GKG)',
                'summary' => 'Teknik pemanenan pada kadar air tepat dan metode pengeringan gabah untuk mencegah beras pecah dan menaikkan harga jual.',
                'tags' => ['pasca panen', 'gkg', 'kadar air', 'pengeringan', 'beras'],
                'is_featured' => false,
                'content_markdown' => "### Penanganan Pasca Panen Padi

Penanganan pasca panen yang tepat dapat menekan kehilangan hasil (loss) dari 10% menjadi kurang dari 2%.

1. **Waktu Panen Ideal**:
   - Panen dilakukan saat 90-95% bulir padi pada malai telah menguning.
   - Kadar air gabah saat panen idealnya 21-24%.

2. **Pembersihan & Perontokan**:
   - Gunakan Power Thresher atau Combine Harvester untuk merontokkan gabah dari malai.

3. **Pengeringan Gabah (Drying)**:
   - Keringkan gabah hingga kadar air **14%** (Gabah Kering Simpan / GKS) atau **12-13%** (Gabah Kering Giling / GKG).
   - Ketebalan hamparan penjemuran di lantai jemur idealnya 3-5 cm dengan pengadukan tiap 2 jam.",
            ],
        ];

        $count = 0;
        foreach ($articles as $art) {
            AgricultureKnowledge::updateOrCreate(
                ['slug' => Str::slug($art['title'])],
                array_merge($art, ['slug' => Str::slug($art['title'])])
            );
            $count++;
        }

        return $count;
    }

    public function getAllArticles(?string $category = null, ?string $search = null)
    {
        return AgricultureKnowledge::query()
            ->when($category, fn($q) => $q->where('category', $category))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('summary', 'like', "%{$search}%")
                        ->orWhere('content_markdown', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->get();
    }

    public function getBySlug(string $slug): ?AgricultureKnowledge
    {
        return AgricultureKnowledge::where('slug', $slug)->first();
    }
}
