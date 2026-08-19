# P.A.D.I. — Backend REST API & Admin Management

Predictive Agriculture & Disease Intelligence (P.A.D.I.) — Backend REST API dibangun menggunakan **Laravel 12 (PHP 8.2+)**, **MySQL**, dan arsitektur modular untuk mendukung aplikasi mobile Flutter dan Dashboard Admin Web.

---

## 📌 Dataset & Spesifikasi Data Sistem

Platform P.A.D.I. mengolah dan menyajikan data terintegrasi berikut:

### 1. Data Spasial & Batas Administrasi Nasional (GeoJSON RFC 7946)
- **38 Provinsi**: Data batas polygon provinsi di seluruh Indonesia.
- **514 Kabupaten / Kota**: Seluruh kabupaten/kota di Indonesia dengan koordinat MultiPolygon/Polygon standar OGC.
- **7.264 Kecamatan**: Batas polygon kecamatan lengkap di 38 provinsi dari sumber terverifikasi Kepmendagri/BPS.
- **Titik & Polygon Lahan Pertanian**: Boundary lahan presisi per petani untuk kalkulasi spasial risiko cuaca dan geofencing penyakit.

### 2. Data Agroklimat & Prakiraan Cuaca Presisi
- **Integrasi**: BMKG Open Data & Open-Meteo Agro API.
- **Parameter**: Suhu udara (°C), kelembaban (RH %), curah hujan harian & mingguan (mm), kecepatan angin (km/h), radiasi matahari, dan evapotranspirasi acuan ($ET_0$).
- **Pemanfaatan**: Rekomendasi jendela tanam ideal, estimasi risiko kekeringan (*drought*), dan potensi banjir (*flood*).

### 3. Data Kalender Tanam Konkret & Varietas Padi
- **Katalog Varietas**: Inpari 32 HDB (115 hari), Inpari 42 GSR (112 hari), Ciherang (116 hari), Mekongga (118 hari), IR64 (115 hari).
- **Perhitungan Konkret**:
  - Tanggal Tanam Riil & Tanggal Panen Pasti.
  - Usia Tanaman Berjalan dalam Hari Setelah Tanam (HST).
  - 5 Fase Pertumbuhan: Semai, Vegetatif Awal, Anakan Maksimum, Bunting (*Heading*), Pematangan (*Ripening*).
  - Tindakan agronomi wajib mingguan per fase.

### 4. Data Monitoring Irigasi & Kebutuhan Air
- **Tipologi Lahan**: Irigasi Teknis, Setengah Teknis, Tadah Hujan, Rawa Pasang Surut.
- **Sistem AWD (Alternate Wetting and Drying)**: Pengaturan ketinggian genangan air (2-5 cm) dan pengeringan berkala untuk efisiensi air 25%.
- **Sistem Notifikasi & Alert**: Peringatan kekurangan air, jadwal buka/tutup pintu air rawa, dan rekomendasi pompa air.

### 5. Data Citra Penyakit & AI Computer Vision
- **Model Deep Learning**: Klasifikasi 4 penyakit daun padi utama (*Blas*, *Hawar Daun Bakteri*, *Tungro*, *Bercak Coklat*) + Daun Sehat.
- **Early Warning System (EWS)**: Geofencing radius peringatan dini penyebaran penyakit (5 - 25 km).

### 6. Data Usaha Tani & Marketplace
- Pencatatan biaya operasional, pendapatan, dan panen.
- Katalog komoditas gabah/beras dan saprodi (benih, pupuk, alsintan).

---

## 🚀 Perintah Artisan Khusus Geo & Pertanian

```bash
# Import batas administrasi 38 provinsi & 514 kabupaten/kota
php artisan geo:import-provinces

# Import 7.264 batas polygon kecamatan seluruh Indonesia
php artisan geo:import-district-boundaries --all

# Auto-generate siklus musim tanam untuk seluruh lahan terdaftar
php artisan tinker --execute="app(\App\Services\Agriculture\CropSeasonService::class)->autoGenerateAllFarmsCropSeasons();"
```
