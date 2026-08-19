P.A.D.I.

Predictive Agriculture & Disease Intelligence — platform digital cerdas untuk mendukung pertanian padi berkelanjutan dari tahap pra-tanam hingga pemasaran hasil panen.

P.A.D.I. dikembangkan oleh Team Fantastic, Politeknik Negeri Indramayu, untuk Hackathon KMIPN VIII 2026. Platform ini membantu petani mengambil keputusan berbasis data melalui rekomendasi waktu tanam, deteksi penyakit daun padi, peringatan dini berbasis lokasi, pencatatan usaha tani, dan marketplace hasil panen.

Daftar isi

Tentang proyek

Permasalahan

Solusi yang ditawarkan

Fitur utama

Ruang lingkup MVP

Arsitektur sistem

Teknologi

Struktur monorepo

Alur utama aplikasi

Persiapan development

Menjalankan proyek

Konfigurasi environment

API

AI dan model deteksi penyakit

Testing

Workflow Git

Pembagian tim

Roadmap

Dokumentasi

Disclaimer

Lisensi

Tentang proyek

Petani padi masih menghadapi proses pengambilan keputusan yang terpisah dan banyak bergantung pada pengalaman, informasi umum, atau ketersediaan Penyuluh Pertanian Lapangan. Informasi cuaca belum selalu diterjemahkan menjadi tindakan yang mudah dipahami, penyakit tanaman sering terlambat dikenali, dan pencatatan biaya serta hasil panen masih belum terstruktur.

P.A.D.I. menyatukan kebutuhan tersebut dalam aplikasi Android yang dapat digunakan dengan kamera, GPS, dan koneksi internet pada smartphone, tanpa mewajibkan perangkat IoT tambahan.

Product statement

Untuk petani padi skala kecil yang memerlukan keputusan cepat tetapi memiliki akses penyuluh terbatas, P.A.D.I. adalah asisten budidaya berbasis data yang mengubah informasi cuaca, foto daun, lokasi, dan catatan lahan menjadi rekomendasi praktis serta mudah dipahami.

Permasalahan

P.A.D.I. berfokus pada tiga masalah utama:

Ketidakpastian waktu tanam akibat perubahan cuaca dan informasi iklim yang masih terlalu umum.

Keterlambatan deteksi penyakit karena gejala penyakit padi sulit dibedakan secara visual dan keterbatasan jumlah penyuluh.

Lemahnya pengelolaan usaha dan posisi tawar petani karena pencatatan biaya, hasil, harga, dan akses pasar belum terintegrasi.

Solusi yang ditawarkan

P.A.D.I. membangun satu ekosistem digital yang menghubungkan:

data cuaca dan rekomendasi waktu tanam;

pengelolaan lahan dan musim tanam;

kalkulator kebutuhan pupuk;

jurnal aktivitas budidaya;

deteksi penyakit berbasis Computer Vision;

rekomendasi tindakan yang mudah dipahami;

peringatan dini penyakit berbasis geolokasi;

pencatatan biaya, pendapatan, dan hasil panen;

marketplace yang mempertemukan petani dan pembeli.

## Fitur utama

1. Predictive Farming
- Menampilkan cuaca saat ini dan prakiraan tujuh hari berbasis BMKG dan Open-Meteo.
- Memberikan status rekomendasi tanam konkret: baik, waspada, atau tunda.
- Menjelaskan faktor agroklimat yang memengaruhi rekomendasi tanam dan irigasi.

2. Manajemen Lahan, Musim Tanam, & Notifikasi Irigasi
- Menambahkan lahan berdasarkan luas, titik polygon GIS, varietas, dan sistem irigasi (Teknis, Setengah Teknis, Tadah Hujan, Rawa).
- Kalender waktu tanam konkret: menghitung Hari Setelah Tanam (HST), fase pertumbuhan riil, estimasi tanggal panen pasti, dan milestone agronomi mingguan.
- Pusat Pemberitahuan Irigasi: monitoring ketersediaan air dan rekomendasi pengairan berselang (AWD).

3. Kalkulator Pupuk & Nutrisi
- Menghitung kebutuhan pupuk presisi berdasarkan luas lahan dan fase spesifik tanaman padi.
- Mendukung formulasi Urea, NPK, dan KCl sesuai dosis anjuran Kementerian Pertanian.

4. Scan Penyakit AI & Community Early Warning
- Deteksi penyakit daun padi berbasis Computer Vision (Blas, Hawar Daun Bakteri, Tungro, Bercak Coklat).
- Peringatan dini penyebaran penyakit berbasis radius geospasial bagi petani sekitar.

5. Keuangan Usaha Tani & Marketplace
- Pencatatan biaya operasional, pendapatan, dan laporan hasil panen terstruktur.
- Marketplace langsung yang mempertemukan petani dengan pembeli komoditas gabah dan beras.

## Dataset & Sumber Data yang Digunakan

Aplikasi **P.A.D.I. (Predictive Agriculture & Disease Intelligence)** mengintegrasikan berbagai lapisan data spasial, agroklimat, agronomi, dan citra kecerdasan buatan:

### 1. Data Geospasial & Batas Administrasi Nasional
- **Cakupan Spasial**: 38 Provinsi, 514 Kabupaten/Kota, dan 7.264 Kecamatan di seluruh Indonesia.
- **Format Data**: Standar OGC / RFC 7946 GeoJSON (`Polygon` & `MultiPolygon`) dengan koordinat `[longitude, latitude]`.
- **Sumber Data**: Kepmendagri / BPS via dataset spasial wilayah terverifikasi (`cahyadsn/wilayah_boundaries`).
- **Pemanfaatan**:
  - Peta persebaran risiko cuaca dan hama/penyakit interaktif berbasis Leaflet GIS.
  - Drill-down bertingkat dari level nasional &rarr; provinsi &rarr; kabupaten &rarr; kecamatan &rarr; polygon lahan petani (*field boundary mapping*).
  - Geofencing radius peringatan dini wabah penyakit (Community Early Warning System radius 5-25 km).

### 2. Data Agroklimat & Prakiraan Cuaca
- **Sumber Data**: BMKG (Badan Meteorologi, Klimatologi, dan Geofisika) Open Data & Open-Meteo Agro Weather API.
- **Parameter yang Digunakan**:
  - Suhu Udara Rata-rata, Minimum, dan Maksimum (°C).
  - Kelembaban Relatif Udara (RH %).
  - Curah Hujan Harian, Akumulasi Mingguan, dan Probabilitas Hujan (mm/hari).
  - Kecepatan & Arah Angin (km/jam).
  - Evapotranspirasi Acuan ($ET_0$) dan Indeks Radiasi Surya (MJ/m²/hari).
- **Pemanfaatan**:
  - Penentuan jendela waktu tanam ideal (Maju / Tepat Waktu / Tunda).
  - Model prediksi risiko kekeringan (*drought risk*) dan banjir (*flood risk*).

### 3. Data Kalender Tanam Konkret & Varietas Padi
- **Katalog Varietas Unggul**:
  - Inpari 32 HDB (115 Hari, Tahan Blas & Hawar Daun Bakteri).
  - Inpari 42 Agritan GSR (112 Hari, Hemat Air & Tahan Rebah).
  - Ciherang (116 Hari, Pulen & Adaptif Irigasi Teknis).
  - Mekongga (118 Hari), IR64 (115 Hari), dan varietas lokal bersertifikat.
- **Siklus 5 Fase Pertumbuhan Terhitung Sejak Hari Setelah Tanam (HST)**:
  - **Fase Semai & Olah Tanah** (H-21 s/d H-1): Pembajakan & perendaman benih.
  - **Fase Vegetatif Awal** (1 - 30 HST): Tanam pindah, perakaran, pemupukan dasar.
  - **Fase Vegetatif Aktif / Anakan Maksimum** (31 - 55 HST): Pembentukan anakan produktif, pemupukan susulan II.
  - **Fase Bunting & Pembungaan / Generatif** (56 - 85 HST): *Panicle initiation* & *heading*, fase kritis kebutuhan air.
  - **Fase Pematangan Bulir & Panen** (86 - 115 HST): *Milky*, *dough*, *mature grain*, pengeringan lahan menjelang panen.

### 4. Data Monitoring Irigasi & Kebutuhan Air (AWD System)
- **Tipologi Lahan**: Irigasi Teknis, Setengah Teknis, Tadah Hujan, Rawa Pasang Surut.
- **Sistem Irigasi Berselang (Alternate Wetting and Drying - AWD)**:
  - Fase Vegetatif: Tinggi muka air genangan 2-3 cm.
  - Fase Anakan & Bunting: Genangan air 3-5 cm.
  - Fase Pematangan: Pengeringan bertahap 10-14 hari sebelum panen.
- **Pemberitahuan & Peringatan Otomatis**:
  - Peringatan dini kekurangan air untuk lahan tadah hujan.
  - Rekomendasi pengaturan pintu tabat/air untuk lahan rawa.
  - Notifikasi rotasi jadwal air untuk efisiensi konsumsi air hingga 25%.

### 5. Data Citra Penyakit & Model AI (Computer Vision)
- **Dataset Citra Daun Padi**: Ribuan sampel citra daun padi berkualitas tinggi dengan augmentasi data kondisi lapangan Indonesia.
- **Kelas Penyakit**:
  1. Blas Daun (*Pyricularia oryzae* / *Magnaporthe oryzae*).
  2. Hawar Daun Bakteri / Kresek (*Xanthomonas oryzae pv. oryzae*).
  3. Tungro (*Rice Tungro Bacilliform Virus* - RTBV).
  4. Bercak Coklat (*Bipolaris oryzae*).
  5. Daun Sehat (*Healthy*).
- **Rekomendasi Tindakan**: Panduan teknis pengendalian terpadu (Kultur teknis, Hayati, dan Kimiawi berizin Kementan).

### 6. Data Komoditas, Sarana Produksi, & Marketplace
- **Katalog Komoditas**: Gabah Kering Panen (GKP), Gabah Kering Giling (GKG), Beras Medium, Beras Premium.
- **Sarana Produksi (Saprodi)**: Pupuk Bersubsidi & Non-Subsidi (Urea, NPK Phonska, SP-36, ZA, KCl, Organik), Benih Bersertifikat, Alat Mesin Pertanian (Alsintan).

4. AI Disease Detection

Mengambil foto daun melalui kamera atau galeri.

Melakukan pemeriksaan kualitas gambar sebelum inferensi.

Mendeteksi kelas Sehat, Blast, Tungro, dan Hawar Daun Bakteri (HDB).

Menampilkan confidence, gejala pembanding, rekomendasi awal, dan panduan foto ulang.

5. Community Early Warning System

Mengubah hasil scan menjadi laporan komunitas setelah memperoleh persetujuan pengguna.

Mengirimkan peringatan kepada petani di sekitar lokasi kejadian.

Memburamkan koordinat publik untuk melindungi privasi lahan.

Mendukung proses verifikasi oleh PPL atau administrator.

6. Jurnal dan keuangan usaha tani

Mencatat aktivitas, biaya, foto, dan catatan budidaya.

Mencatat pemasukan serta pengeluaran setiap musim tanam.

Menghitung total biaya, pendapatan, margin, dan biaya per hektare.

7. Marketplace hasil panen

Membuat dan mempublikasikan listing hasil panen.

Menampilkan varietas, jumlah, harga, kualitas, dan estimasi waktu panen.

Memungkinkan pembeli mencari hasil panen dan menghubungi petani.

MVP tidak memproses pembayaran atau escrow di dalam aplikasi.

Ruang lingkup MVP

Prioritas

Modul

Implementasi

P0

Autentikasi, profil, lahan, dan musim tanam

API dan database nyata

P0

Dashboard cuaca dan rekomendasi tanam

Weather API dengan cache dan fallback

P0

Scan penyakit dan rekomendasi tindakan

Inferensi model nyata

P0

Community Early Warning System

Laporan, radius, peta, dan notifikasi

P0

Kalkulator pupuk dan jurnal budidaya

CRUD dan formula terkonfigurasi

P1

Keuangan usaha tani

Pemasukan, pengeluaran, dan ringkasan

P1

Marketplace hasil panen

Listing, pencarian, detail, dan kontak

P2

Dashboard PPL dan moderasi lanjutan

Pengembangan setelah MVP

P2

Pembayaran, escrow, dan logistik

Di luar cakupan hackathon

Arsitektur sistem

flowchart TB
    Mobile[Flutter Android] -->|HTTPS / JSON| API[Laravel REST API]
    API --> DB[(MySQL)]
    API --> Storage[(Object Storage)]
    API --> Queue[Queue Worker]
    API --> ML[Python ML Service]
    API --> Weather[Weather API]
    Queue --> FCM[Firebase Cloud Messaging]
    API --> LLM[LLM Provider / Template Fallback]

Prinsip arsitektur:

Aplikasi Flutter hanya berkomunikasi dengan Laravel REST API.

Laravel menjadi pusat autentikasi, otorisasi, domain bisnis, audit, dan integrasi.

ML service tidak diekspos langsung ke aplikasi mobile.

Proses lambat seperti inferensi, notifikasi, dan clustering dapat dijalankan melalui queue.

Data cuaca dan rekomendasi memiliki cache serta fallback agar demo tetap stabil.

Waktu disimpan dalam UTC dan ditampilkan menggunakan zona waktu Asia/Jakarta.

Teknologi

Lapisan

Teknologi

Mobile

Flutter, Dart

Backend API

Laravel, PHP

Database

MySQL

AI/ML service

Python, FastAPI, CNN/YOLO-compatible model

Authentication

Laravel Sanctum

Notification

Firebase Cloud Messaging

Weather

Weather API melalui provider adapter

AI recommendation

Knowledge base terkurasi dengan LLM opsional

File storage

Local/S3-compatible object storage

API contract

OpenAPI 3

Development

Docker Compose, GitHub Actions

Struktur monorepo

padi-platform/
├── apps/
│   ├── mobile/                    # Aplikasi Flutter Android
│   └── api/                       # Laravel REST API
├── services/
│   └── disease-inference/         # FastAPI dan model deteksi penyakit
├── packages/
│   ├── api-contracts/             # OpenAPI dan contoh payload
│   └── design-tokens/             # Warna, spacing, dan aset bersama
├── datasets/                      # Manifest dan script pengolahan dataset
├── docs/                          # PRD, ERD, ADR, model card, dan demo
├── infra/                         # Docker dan konfigurasi deployment
├── .github/
│   ├── workflows/                 # CI/CD
│   └── ISSUE_TEMPLATE/            # Template issue
├── docker-compose.yml
├── .env.example
├── CONTRIBUTING.md
└── README.md

Struktur di atas merupakan target monorepo. Folder dapat ditambahkan bertahap sesuai progres pengembangan.

Alur utama aplikasi

Mulai musim tanam

Daftar → Tambah Lahan → Lihat Cuaca → Lihat Rekomendasi → Aktifkan Musim Tanam

Scan dan peringatan penyakit

Foto Daun → Quality Check → Inferensi → Rekomendasi → Persetujuan → Community Alert

Pengelolaan usaha tani

Catat Aktivitas → Catat Biaya → Pantau Musim → Catat Hasil → Evaluasi Margin

Marketplace

Buat Listing → Publikasikan → Ditemukan Pembeli → Hubungi Petani → Tutup Listing

Persiapan development

Prasyarat

Pastikan perangkat development memiliki:

Git;

Docker dan Docker Compose;

Flutter SDK versi stabil;

Android Studio atau Android SDK;

PHP dan Composer sesuai versi Laravel yang dipakai;

Python 3.11 atau lebih baru;

MySQL 8 jika tidak menggunakan Docker.

Clone repository

git clone https://github.com/ORGANIZATION_OR_USERNAME/padi-platform.git
cd padi-platform

Ganti ORGANIZATION_OR_USERNAME dengan pemilik repository yang sebenarnya.

Menjalankan proyek

Opsi 1 — Docker Compose

Setelah konfigurasi Docker tersedia:

cp .env.example .env
docker compose up -d --build

Periksa layanan:

docker compose ps
docker compose logs -f api
docker compose logs -f disease-inference

Opsi 2 — Menjalankan Laravel secara lokal

cd apps/api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve

Jalankan queue worker pada terminal lain:

cd apps/api
php artisan queue:work --tries=3 --timeout=120

Menjalankan ML service

cd services/disease-inference
python -m venv .venv

Aktifkan virtual environment.

Linux/macOS:

source .venv/bin/activate

Windows PowerShell:

.venv\Scripts\Activate.ps1

Instal dependensi dan jalankan service:

pip install -r requirements.txt
uvicorn app.main:app --reload --host 0.0.0.0 --port 8000

Menjalankan aplikasi Flutter

cd apps/mobile
flutter pub get
flutter doctor
flutter run

Untuk membuat APK:

flutter build apk --release

Konfigurasi environment

Jangan memasukkan API key, credential, atau file rahasia ke repository.

Contoh variabel environment backend:

APP_NAME=PADI
APP_ENV=local
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=padi
DB_USERNAME=padi
DB_PASSWORD=

ML_SERVICE_URL=http://disease-inference:8000
ML_SERVICE_TOKEN=

WEATHER_PROVIDER=
WEATHER_API_KEY=

FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

LLM_MODE=template
GEMINI_API_KEY=

FCM_CREDENTIALS_JSON=
DEMO_MODE=true

Konfigurasi URL API pada Flutter sebaiknya menggunakan --dart-define:

flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api/v1

API

Seluruh endpoint versi pertama menggunakan prefix:

/api/v1

Endpoint utama:

Method

Endpoint

Fungsi

POST

/auth/register

Registrasi pengguna

POST

/auth/login

Login dan membuat token

GET

/me

Mengambil profil pengguna

GET/POST

/farms

Daftar dan tambah lahan

POST

/farms/{farm}/seasons

Membuat musim tanam

GET

/farms/{farm}/weather

Mengambil cuaca lahan

GET

/farms/{farm}/planting-recommendation

Rekomendasi waktu tanam

POST

/fertilizer/calculate

Menghitung kebutuhan pupuk

GET/POST

/seasons/{season}/activities

Jurnal budidaya

POST

/disease-scans

Mengirim gambar untuk dipindai

GET

/disease-scans/{scan}

Melihat status dan hasil scan

POST

/disease-scans/{scan}/report

Membuat laporan komunitas

GET

/community-alerts

Mengambil peringatan sekitar

GET/POST

/seasons/{season}/financial-entries

Keuangan usaha tani

GET/POST

/market-listings

Listing marketplace

Format respons API:

{
  "success": true,
  "message": "Pemindaian selesai",
  "data": {
    "id": "scan_uuid",
    "status": "completed"
  },
  "meta": {
    "request_id": "req_uuid",
    "timestamp": "2026-08-10T12:00:00Z"
  }
}

Spesifikasi lengkap disimpan di packages/api-contracts/openapi.yaml.

AI dan model deteksi penyakit

Kelas awal model:

Sehat

Blast

Tungro

Hawar Daun Bakteri atau HDB

Tidak dapat dipastikan

Aturan confidence

Confidence

Status UI

Tindakan

>= 0.80

Kemungkinan kuat

Tampilkan hasil dan rekomendasi dengan disclaimer

0.55–0.79

Perlu konfirmasi

Tampilkan kandidat dan sarankan foto tambahan/PPL

< 0.55

Belum dapat dipastikan

Jangan membuat alert otomatis; minta foto ulang

Evaluasi model

Model tidak dinilai hanya berdasarkan nilai training. Setiap versi model harus memiliki:

precision, recall, dan F1-score untuk setiap kelas;

macro F1-score;

confusion matrix;

jumlah sampel train, validation, dan test;

pengujian foto lapangan;

versi dataset dan model;

model card yang menjelaskan tujuan, batasan, dan threshold.

File model berukuran besar tidak disimpan langsung melalui Git biasa. Gunakan object storage, Git LFS, atau DVC sesuai keputusan tim.

Testing

Backend Laravel

cd apps/api
php artisan test

Area pengujian backend:

autentikasi dan authorization policy;

validasi request;

manajemen lahan dan musim tanam;

formula pupuk dan rekomendasi tanam;

upload serta status pemindaian;

laporan komunitas dan radius alert;

perhitungan keuangan;

marketplace dan ownership.

Flutter

cd apps/mobile
flutter analyze
flutter test

ML service

cd services/disease-inference
pytest

Pemeriksaan sebelum demo

Jalankan aplikasi pada minimal dua perangkat Android.

Uji alur login sampai hasil scan.

Uji notifikasi menggunakan dua akun petani.

Uji cache atau fallback ketika provider eksternal gagal.

Pastikan seed data dan foto demo sudah tersedia.

Siapkan rekaman demo lokal sebagai cadangan.

Workflow Git

Branch utama

main — selalu stabil dan siap didemokan.

develop — tempat integrasi fitur sebelum masuk main.

feat/* — pengembangan fitur.

fix/* — perbaikan bug.

docs/* — dokumentasi.

chore/* — konfigurasi atau pekerjaan nonfitur.

Contoh nama branch:

feat/SCAN-01-camera-quality-check
feat/ALERT-02-community-report
fix/AUTH-03-token-expiration
docs/update-api-contract

Format commit

Gunakan Conventional Commits dan sertakan ID requirement jika tersedia:

feat(scan): add asynchronous inference status [SCAN-03]
fix(alert): prevent duplicate radius notification [ALERT-05]
docs(readme): update local development instructions
test(farm): add fertilizer calculation cases [FARM-02]

Pull request

Sebelum pull request digabungkan:

acceptance criteria sudah terpenuhi;

lint dan test lulus;

tidak ada secret atau credential;

API contract diperbarui bila diperlukan;

screenshot atau video dilampirkan untuk perubahan UI;

minimal satu anggota tim melakukan review;

branch sudah sinkron dengan develop.

Pembagian tim

Pembagian awal dapat disesuaikan berdasarkan kekuatan teknis setiap anggota.

Anggota

Peran

Tanggung jawab utama

Faiz Jihad Al Baihaqi — 2403078

Product & Backend Lead

Scope, Laravel API, database, integrasi, deployment, dan demo orchestration

Audy Zahra Aditya Putri — 2403023

Mobile & UX Lead

Flutter, design system, kamera, GPS, cache, state management, dan mobile testing

Yolanda Nurul Haq — 2403021

AI/ML & Quality Lead

Dataset, model, inference service, model card, metric, quality gate, dan test evidence

Aturan kerja tim:

daily sync maksimal 15 menit;

satu Directly Responsible Individual untuk setiap deliverable;

pull request dibuat kecil dan fokus;

keputusan arsitektur penting dicatat sebagai Architecture Decision Record;

integrasi end-to-end dimulai sejak awal, bukan menunggu semua modul selesai.

Roadmap

Hackathon MVP — 14 hari

Hari 1 — Freeze scope, repository, issue board, dan kontrak API awal

Hari 2–3 — Autentikasi, lahan, musim tanam, Flutter shell, dan ML health check

Hari 4–5 — Weather, dashboard, kalkulator pupuk, dan jurnal

Hari 6–7 — Upload gambar, inferensi, hasil AI, dan fallback rekomendasi

Hari 8–9 — Community report, blur lokasi, radius alert, dan notifikasi

Hari 10–11 — Keuangan usaha tani dan marketplace

Hari 12 — Testing, authorization, offline state, dan error handling

Hari 13 — Demo rehearsal, seed data, APK release candidate, dan backup video

Hari 14 — Release, dokumentasi, dan presentasi

Setelah hackathon

Validasi bersama petani dan Penyuluh Pertanian Lapangan

Menambah data lapangan dari perangkat serta kondisi pencahayaan berbeda

Dashboard PPL untuk verifikasi laporan dan pemantauan klaster

Pengembangan multiwilayah dan multikomoditas

Integrasi mitra agribisnis dan model bisnis berkelanjutan

Peningkatan sistem prediksi menggunakan data historis lahan

Dokumentasi

Dokumentasi pengembangan direncanakan tersedia pada folder docs/:

docs/PRD.html — Product Requirements Document;

docs/architecture.md — arsitektur sistem;

docs/erd.md — model dan relasi database;

docs/api.md — panduan integrasi API;

docs/model-card.md — informasi model deteksi penyakit;

docs/demo/runbook.md — skenario dan checklist demo;

docs/adr/ — Architecture Decision Record.

Disclaimer

P.A.D.I. merupakan sistem pendukung keputusan dan bukan pengganti diagnosis resmi dari Penyuluh Pertanian Lapangan, agronom, atau laboratorium.

Hasil deteksi penyakit bersifat prediktif dan dipengaruhi oleh kualitas gambar, kondisi tanaman, komposisi dataset, serta versi model. Ketika tingkat keyakinan rendah, aplikasi harus menyarankan pengambilan foto ulang atau konsultasi dengan tenaga ahli.

Rekomendasi penggunaan pestisida, bahan aktif, atau dosis tidak boleh diberikan tanpa sumber terverifikasi dan validasi pakar.

Kontribusi

Repository ini dikembangkan untuk kebutuhan Team Fantastic. Prosedur kontribusi internal akan dijelaskan pada CONTRIBUTING.md.

Untuk melaporkan bug atau mengusulkan fitur, gunakan GitHub Issues dan sertakan:

deskripsi masalah atau kebutuhan;

langkah reproduksi;

hasil yang diharapkan;

hasil aktual;

screenshot atau log yang sudah disensor;

perangkat dan versi aplikasi.

Lisensi

Status lisensi akan ditentukan oleh Team Fantastic sebelum publikasi repository. Seluruh dataset, model, gambar, logo, dan layanan pihak ketiga tetap mengikuti lisensi serta ketentuan sumber masing-masing.

<div align="center">
  <strong>Team Fantastic</strong><br>
  Politeknik Negeri Indramayu<br>
  KMIPN VIII 2026
</div>
