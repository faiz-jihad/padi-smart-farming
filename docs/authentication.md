# Autentikasi P.A.D.I.

Dokumen ini merangkum fondasi autentikasi Laravel + Flutter.

## Backend Laravel

Lokasi project:

```bash
cd Backend/backend-apk-padi
```

Jalankan setup lokal:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Sanctum menggunakan Bearer Token. Flutter wajib mengirim:

```http
Authorization: Bearer {token}
Accept: application/json
```

Endpoint v1:

```text
POST   /api/v1/auth/register
POST   /api/v1/auth/login
GET    /api/v1/auth/me
POST   /api/v1/auth/logout
POST   /api/v1/auth/logout-all
GET    /api/v1/profile
PATCH  /api/v1/profile
PUT    /api/v1/profile/password
POST   /api/v1/auth/forgot-password
POST   /api/v1/auth/reset-password
```

Register publik hanya menerima `farmer` dan `buyer`. Role `admin` dan `extension_officer` disiapkan melalui seeder internal Spatie Permission.

Contoh register:

```json
{
  "name": "Budi Santoso",
  "email": "budi@example.com",
  "phone": "081234567890",
  "password": "PasswordKuat123",
  "password_confirmation": "PasswordKuat123",
  "account_type": "farmer",
  "device_name": "Samsung A54"
}
```

Contoh respons sukses:

```json
{
  "success": true,
  "message": "Login berhasil.",
  "data": {
    "user": {
      "id": 1,
      "name": "Budi Santoso",
      "email": "budi@example.com",
      "phone": "081234567890",
      "role": "farmer",
      "role_label": "Petani",
      "status": "active",
      "status_label": "Aktif"
    },
    "token": "plain-text-token-hanya-pada-register-atau-login"
  }
}
```

Forgot/reset password akan mengembalikan `503` bila mailer masih `log` atau `array`. Konfigurasikan mailer production sebelum mengaktifkan alur reset sungguhan.

## Flutter

Lokasi project:

```bash
cd Frontend/apk_padi
flutter pub get
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api/v1
```

Gunakan `10.0.2.2` untuk emulator Android saat Laravel berjalan di komputer pengembang.

Pada Windows, `flutter_secure_storage` membutuhkan symlink plugin. Aktifkan Developer Mode bila `flutter pub get` menampilkan pesan:

```text
Building with plugins requires symlink support.
```

## Testing

Backend:

```bash
cd Backend/backend-apk-padi
php artisan test
```

Flutter:

```bash
cd Frontend/apk_padi
flutter analyze
flutter test
```
