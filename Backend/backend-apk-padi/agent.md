# Backend Agent Guide

Dokumen ini menjadi aturan kerja untuk agent yang mengubah backend Laravel P.A.D.I.

## Prinsip

- Controller harus tipis: validasi lewat FormRequest, logic aplikasi di service, response di controller.
- Jangan taruh query bisnis langsung di controller baru.
- Jangan membuat data statis untuk fitur admin. Semua data admin harus berasal dari database.
- Perubahan admin harus menulis audit log bila mengubah data penting.
- Notifikasi admin harus lewat `AdminNotificationService` agar tetap tersambung ke WebSocket/Reverb.
- Pertahankan satu route API admin di `routes/api.php`: `api/v1/admin/{resource?}/{id?}`.
- Jangan mengubah migrasi lama tanpa alasan kuat; tambah migrasi baru bila perlu perubahan schema.
- Jangan hardcode role display tanpa memperhatikan mapping legacy:
  - FE/API `buyer` sama dengan DB `partner`.
  - FE/API `extension_officer` sama dengan DB `ppl`.

## Area Yang Harus Dijaga

- Auth API: `app/Domain/Auth/Actions`, `app/Services/AuthSessionService.php`.
- Admin web: `app/Http/Controllers/Admin`, `app/Services/Admin`, `resources/views/admin`.
- Admin API: `app/Services/Admin/AdminApiService.php`.
- Realtime notification: `app/Events/AdminNotificationCreated.php`, `routes/channels.php`, `resources/js/echo.js`.

## Pola Implementasi

1. Buat FormRequest untuk validasi input HTTP.
2. Buat atau pakai service untuk logic.
3. Controller hanya memanggil service dan mengembalikan response.
4. Tambahkan test feature untuk flow penting.
5. Jalankan `vendor/bin/pint` dan `php artisan test`.

## Larangan

- Jangan buat controller jumbo seperti `OperationsController`.
- Jangan simpan business rule di Blade.
- Jangan buat mock data di Blade admin.
- Jangan mem-bypass middleware admin.
- Jangan mematikan Reverb/Echo untuk notifikasi realtime.
