# Panduan Menjalankan Semua Service P.A.D.I.
**Predictive Agriculture & Disease Intelligence**

Dokumen ini berisi panduan langkah demi langkah untuk mengonfigurasi dan menjalankan seluruh service dalam ekosistem platform **P.A.D.I.**, termasuk integrasi real-time WebSockets (Laravel Reverb), kebijakan Rate Limiting (Throttling), proteksi N+1 Query, dan transaksi database multi-tabel.

---

## 📑 Daftar Isi
1. [Arsitektur & Pemetaan Port](#1-arsitektur--pemetaan-port)
2. [Prasyarat Sistem](#2-prasyarat-sistem)
3. [Cara Cepat: 1-Click Run (Batch Script)](#3-cara-cepat-1-click-run-batch-script)
4. [Cara Manual: Step-by-Step](#4-cara-manual-step-by-step)
   - [Langkah 1: Database (MySQL)](#langkah-1-database-mysql)
   - [Langkah 2: AI Microservice (FastAPI - Port 8003)](#langkah-2-ai-microservice-fastapi---port-8003)
   - [Langkah 3: WebSocket Server (Laravel Reverb - Port 8080)](#langkah-3-websocket-server-laravel-reverb---port-8080)
   - [Langkah 4: Backend REST API (Laravel - Port 8000)](#langkah-4-backend-rest-api-laravel---port-8000)
   - [Langkah 5: Frontend App (Flutter Mobile/Web)](#langkah-5-frontend-app-flutter-mobileweb)
5. [Daftar Channel & Event WebSocket Realtime](#5-daftar-channel--event-websocket-realtime)
6. [Kebijakan Rate Limiting & Proteksi Kuota](#6-kebijakan-rate-limiting--proteksi-kuota)
7. [Integritas Data: Anti-N+1 Query & DB Transactions](#7-integritas-data-anti-n1-query--db-transactions)
8. [Konfigurasi Jaringan & IP Host Flutter](#8-konfigurasi-jaringan--ip-host-flutter)
9. [Verifikasi & Health Check Endpoints](#9-verifikasi--health-check-endpoints)
10. [Troubleshooting & Solusi Masalah Umum](#10-troubleshooting--solusi-masalah-umum)

---

## 1. Arsitektur & Pemetaan Port

```
+-------------------------------------------------------------+
|                      FLUTTER CLIENT                         |
|             (Android App / Emulator / Web / Desktop)        |
+------------------------------+------------------------------+
                               | (HTTP / REST API)
                               v
+-------------------------------------------------------------+
|                    LARAVEL BACKEND API                      |
|                   Port 8000 (0.0.0.0:8000)                  |
+---------------+--------------+--------------+---------------+
                |              |              |
 (Internal REST | Port 8003)   | (Port 8080)  | (Port 3306)
                v              v              v
+-----------------+ +-------------------+ +-------------------+
| AI MICROSERVICE | | WEBSOCKET SERVER  | |  MYSQL DATABASE   |
| FastAPI / YOLO  | |  Laravel Reverb   | | Database: padi_db |
+-----------------+ +-------------------+ +-------------------+
```

| Service | Teknologi | Lokasi Folder | Port / Host | URL Endpoint / Akses |
| :--- | :--- | :--- | :--- | :--- |
| **Database** | MySQL 8 / MariaDB | — | `localhost:3306` | Database: `padi_db` |
| **AI Microservice** | FastAPI, PyTorch (YOLO) | `ai-service/` | `0.0.0.0:8003` | `http://127.0.0.1:8003` (Docs: `/docs`) |
| **WebSocket Server** | Laravel Reverb | `Backend/backend-apk-padi/` | `0.0.0.0:8080` | `ws://127.0.0.1:8080` |
| **Backend REST API** | Laravel 11 (PHP 8.2+) | `Backend/backend-apk-padi/` | `0.0.0.0:8000` | `http://127.0.0.1:8000` (API: `/api/v1`) |
| **Frontend Mobile** | Flutter 3.x | `Frontend/apk_padi/` | — | Perangkat Android / Emulator / Chrome |

---

## 2. Prasyarat Sistem

Pastikan perangkat Anda telah terpasang:
- **PHP** >= 8.2 & **Composer** (untuk Laravel & Reverb)
- **MySQL** >= 8.0 atau **MariaDB** (melalui XAMPP, Laragon, atau Docker)
- **Python** 3.11 (Direkomendasikan versi 3.11 untuk kompatibilitas PyTorch & Ultralytics YOLO)
- **Flutter SDK** >= 3.x & **Android Studio** (untuk build Flutter)
- *(Opsional)* **Docker & Docker Compose**

---

## 3. Cara Cepat: 1-Click Run (Batch Script)

Repository ini telah dilengkapi skrip otomasi **`run_services.bat`** di direktori utama:

1. Pastikan **MySQL** sudah berjalan (misal via XAMPP / Laragon).
2. Double-click file `run_services.bat` atau jalankan di command line:
   ```cmd
   D:\Hackathon KMIPN\run_services.bat
   ```
3. Skrip akan membuka 4 window terminal terpisah secara bersamaan:
   - **AI Microservice** pada port `8003`
   - **WebSocket Reverb Server** pada port `8080`
   - **Backend Laravel** pada port `8000`
   - **Frontend App** (`flutter run`)

---

## 4. Cara Manual: Step-by-Step

### Langkah 1: Database (MySQL)
1. Buka control panel database Anda (XAMPP / Laragon / Docker).
2. Nyalakan service **MySQL**.
3. Buat database baru bernama `padi_db` jika belum ada:
   ```sql
   CREATE DATABASE padi_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

---

### Langkah 2: AI Microservice (FastAPI - Port 8003)

Masuk ke direktori `ai-service`:
```powershell
cd "D:\Hackathon KMIPN\ai-service"
```

#### Opsi A — Menggunakan Python Virtual Environment (Lokal):
```powershell
# 1. Buat virtual environment jika belum ada (gunakan Python 3.11)
python -m venv .venv

# 2. Aktifkan virtual environment (PowerShell)
.venv\Scripts\Activate.ps1

# 3. Install dependency (jika setup baru)
pip install -r requirements.txt

# 4. Salin file .env jika belum ada
copy .env.example .env

# 5. Jalankan server FastAPI pada port 8003
uvicorn app.main:app --host 0.0.0.0 --port 8003 --reload
```

#### Opsi B — Menggunakan Docker Compose:
```powershell
docker compose up -d
```

> **Verifikasi**: Buka browser di [http://127.0.0.1:8003/docs](http://127.0.0.1:8003/docs) untuk melihat dokumentasi interaktif Swagger API.

---

### Langkah 3: WebSocket Server (Laravel Reverb - Port 8080)

Buka terminal baru untuk menjalankan Reverb WebSocket Server:
```powershell
cd "D:\Hackathon KMIPN\Backend\backend-apk-padi"
php artisan reverb:start --host=0.0.0.0 --port=8080 --debug
```

---

### Langkah 4: Backend REST API (Laravel - Port 8000)

Buka terminal baru dan masuk ke direktori backend:
```powershell
cd "D:\Hackathon KMIPN\Backend\backend-apk-padi"
```

#### Setup Awal (Hanya dilakukan pada instalasi pertama kali):
```powershell
# 1. Install library PHP
composer install

# 2. Salin .env jika belum ada
copy .env.example .env

# 3. Generate APP_KEY
php artisan key:generate

# 4. Pastikan konfigurasi .env sesuai:
#    DB_DATABASE=padi_db
#    DB_USERNAME=root
#    DB_PASSWORD=
#    AI_SERVICE_URL=http://127.0.0.1:8003/api/v1
#    BROADCAST_CONNECTION=reverb
#    REVERB_PORT=8080

# 5. Jalankan migrasi database beserta seeder data demo
php artisan migrate --seed

# 6. Buat link symbolic storage publik (agar foto/gambar dapat diakses)
php artisan storage:link
```

#### Jalankan Server Laravel:
```powershell
php artisan serve --host=0.0.0.0 --port=8000
```

*(Opsional)* Jalankan queue worker di terminal terpisah jika menggunakan fitur antrean background:
```powershell
php artisan queue:work --tries=3 --timeout=120
```

---

### Langkah 5: Frontend App (Flutter Mobile/Web)

Buka terminal baru dan masuk ke direktori frontend:
```powershell
cd "D:\Hackathon KMIPN\Frontend\apk_padi"
```

#### Ambil Paket Dependency & Cek Perangkat:
```powershell
flutter pub get
flutter devices
```

#### Menjalankan Aplikasi:
```powershell
# 1. Jalankan di perangkat default / Emulator yang sedang aktif
flutter run

# 2. Atau jalankan di browser Google Chrome (Web)
flutter run -d chrome

# 3. Atau jalankan di Windows Desktop
flutter run -d windows
```

---

## 5. Daftar Channel & Event WebSocket Realtime

| Channel | Tipe | Event Name | Deskripsi |
| :--- | :--- | :--- | :--- |
| `agri-telemetry` | Publik | `.telemetry.updated` | Siaran data telemetri cuaca & sensor tanah real-time ke web dashboard & app |
| `disaster-alerts` | Publik | `.disaster.alert` | Siaran peringatan dini bencana / outbreak penyakit padi |
| `admin.notifications.{userId}` | Privat | `.admin.notification.created` | Notifikasi instan per pengguna (Petani, PPL, Buyer, Admin) |
| `SSE Stream` | HTTP GET | `/api/v1/realtime/stream` | Stream cadangan SSE (Server-Sent Events) untuk live ticker harga & radar |

---

## 6. Kebijakan Rate Limiting & Proteksi Kuota

Untuk menjaga stabilitas sistem dari spam, brute force, dan overload inferensi GPU/AI, berikut aturan rate limiting yang diterapkan:

| Domain / Fitur | Batas Kuota (Throttling) | Scope / Kunci Identifikasi | Status Respon saat Terlampaui |
| :--- | :--- | :--- | :--- |
| **General REST API** | 120 requests / menit | User ID atau IP | HTTP 429 (`Too Many Requests`) |
| **Autentikasi (Login/Register/Reset)** | 10 percobaan / menit | Client IP | HTTP 429 (`auth-strict`) |
| **AI Disease Scanner (YOLO)** | 15 scan / menit | User ID atau IP | HTTP 429 (`ai-scans`) |
| **Soil Sensor Detection** | 20 request / menit | User ID atau IP | HTTP 429 (`soil-scans`) |
| **Weather & External API Refresh** | 30 request / menit | User ID atau IP | HTTP 429 (`weather-refresh`) |
| **Push & Broadcast Notifications** | 20 siaran / menit | User ID atau IP | HTTP 429 (`push-notifications`) |
| **Disaster Early Warning Alert** | 15 broadcast / menit | User ID atau IP | HTTP 429 (`broadcast-alert`) |
| **Marketplace Write (Listing/Order)** | 30 transaksi / menit | User ID atau IP | HTTP 429 (`marketplace-write`) |
| **Community Disease Reports** | 20 laporan / menit | User ID atau IP | HTTP 429 (`community-reports`) |
| **Realtime SSE Stream Inisiasi** | 10 koneksi / menit | User ID atau IP | HTTP 429 (`realtime-stream`) |
| **WebSocket Reverb Channel Auth** | 60 request / menit | User ID atau IP | HTTP 429 (`ws-auth`) |
| **FastAPI AI Microservice** | 60 request / menit | Client IP (Sliding Window) | HTTP 429 (`RATE_LIMIT_EXCEEDED`) |

---

## 7. Integritas Data: Anti-N+1 Query & DB Transactions

### A. Pencegahan N+1 Query (Strict Eager Loading)
Aplikasi mengaktifkan mode strict Eloquent melalui `AppServiceProvider`:
```php
Model::shouldBeStrict(! $this->app->isProduction());
```
- **Prinsip**: Setiap relasi yang dipanggil dalam loop/koleksi diwajibkan menggunakan Eager Loading (`with(...)`, `load(...)`, atau `loadMissing(...)`).
- **Pengecekan Otomatis**: Jika terjadi lazy loading tidak sengaja (N+1 query) pada masa development, Laravel akan langsung memunculkan exception/peringatan sehingga performa sub-50ms tetap terjaga.

### B. Transaksi Database Multi-Tabel (`DB::transaction`)
Semua operasi penulisan data yang melibatkan lebih dari satu tabel atau memicu relasi turunan (seperti notifikasi, audit log, atau pembaruan status) dibungkus secara atomik dalam `DB::transaction(...)`:
1. **Pendaftaran Akun (`RegisterUserAction`)**: Penambahan user baru + sinkronisasi Spatie Roles + penerbitan Sanctum Token.
2. **Transaksi Marketplace (`MarketOfferController` & `PurchaseContractController`)**: Penawaran/kontrak + penguncian listing (`lockForUpdate`) + pengurangan stok panen + pembatalan penawaran lain + pembuatan notifikasi.
3. **Pendaftaran Event (`EventController`)**: Pembuatan tiket pendaftaran + inkremen jumlah peserta terdaftar.
4. **Manajemen Pengguna & Listing Admin (`AdminUserService` & `AdminMarketplaceService`)**: Modifikasi entitas + audit logger + dispatch notifikasi + penghapusan relasi cascade.

---

## 8. Konfigurasi Jaringan & IP Host Flutter

Aplikasi Flutter `apk_padi` memiliki sistem deteksi host cerdas (`AppConfig`). Namun saat menjalankan di perangkat tertentu, sesuaikan parameter berikut:

1. **Android Emulator**:
   - Secara default terhubung otomatis ke `10.0.2.2:8000` (alias `localhost` dari sisi emulator).
2. **HP Android Fisik (USB Debugging / Wi-Fi)**:
   - Pastikan laptop dan HP terhubung ke **Wi-Fi yang sama**.
   - Cek IP IPv4 laptop via perintah `ipconfig` di CMD (misal: `192.168.100.10`).
   - Jalankan Flutter dengan parameter `--dart-define`:
     ```powershell
     flutter run --dart-define=API_LAN_HOST=192.168.100.10
     ```

---

## 9. Verifikasi & Health Check Endpoints

Untuk memastikan semua service terhubung dengan baik, cek endpoint berikut:

| Endpoint | Protocol / Method | Hasil yang Diharapkan |
| :--- | :--- | :--- |
| `http://127.0.0.1:8003/api/v1/health` | GET | `{"success": true, "data": {"status": "ok", "model_loaded": true}}` |
| `http://127.0.0.1:8003/docs` | GET | Tampilan antarmuka Swagger UI FastAPI |
| `http://127.0.0.1:8000/api/v1/health` | GET | `{"status": "ok", "timestamp": "..."}` |
| `ws://127.0.0.1:8080` | WebSocket | Server WebSocket Reverb aktif menerima koneksi |

---

## 10. Troubleshooting & Solusi Masalah Umum

### 1. WebSocket Reverb tidak terkoneksi
- **Penyebab**: Service Reverb belum dijalankan.
- **Solusi**: Jalankan perintah `php artisan reverb:start --host=0.0.0.0 --port=8080 --debug` di folder backend.

### 2. Error: Python ML Dependency / PyTorch gagal dimuat
- **Penyebab**: Menggunakan Python versi terlalu baru (3.13 / 3.14).
- **Solusi**: Gunakan **Python 3.11** untuk environment `ai-service`.

### 3. Error: Flutter gagal terhubung ke Backend (`Connection Refused` / `SocketException`)
- **Penyebab**: Host IP tidak terjangkau dari HP/Emulator atau firewall Windows memblokir port 8000.
- **Solusi**:
  1. Pastikan Laravel dijalankan dengan `--host=0.0.0.0 --port=8000`.
  2. Tambahkan rule inbound Windows Firewall untuk port 8000 dan 8080.
  3. Gunakan flag `--dart-define=API_LAN_HOST=<IP_LAPTOP>` saat `flutter run`.
