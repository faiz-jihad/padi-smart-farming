# Backend Architecture

Backend memakai Laravel 13, Sanctum, Spatie Permission, Reverb, dan Blade admin.

## Layer

### Routes

- `routes/api.php`: endpoint REST mobile dan satu route admin API.
- `routes/web.php`: auth admin dan halaman Blade admin.
- `routes/channels.php`: authorization private channel WebSocket.

### Controllers

Controller hanya bertugas:

- menerima request,
- memanggil FormRequest/service,
- mengembalikan JSON, redirect, atau view.

Controller tidak boleh berisi query bisnis langsung.

### FormRequest

Validasi input HTTP ada di:

- `app/Http/Requests/Api/V1`
- `app/Http/Requests/Admin`

### Services

Logic aplikasi ada di:

- `app/Services/Admin`: admin dashboard, admin auth, admin API, audit, notification, marketplace, broadcast, disease, agriculture, user.
- `app/Services/Api`: service API umum untuk resource list, profile, farm, crop season, password reset.
- `app/Services/AuthSessionService.php`: pengelolaan token Sanctum.

### Domain Actions

Action domain yang sudah ada:

- `app/Domain/Auth/Actions`
- `app/Domain/Farm/Actions`
- `app/Domain/CropSeason/Actions`

Action cocok untuk use case kecil yang spesifik dan dipakai lintas controller.

### Models

Model Eloquent berada di `app/Models`.
Relasi utama:

- `User` punya `farmerProfile`, `farms`, `notifications`.
- `Farm` punya `farmer`, `cropSeasons`.
- `AdminBroadcast` punya `admin`.
- `AuditLog` punya `user`.
- `Notification` punya `user`.

### Realtime

Realtime admin memakai Laravel Reverb.

- Event: `App\Events\AdminNotificationCreated`
- Channel: `admin.notifications.{id}`
- Frontend Blade: `resources/js/echo.js` dan `resources/js/app.js`

`.env` harus memakai:

```env
BROADCAST_CONNECTION=reverb
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

## Testing

Test utama:

- `tests/Feature/Admin/AdminBladeDashboardTest.php`
- `tests/Feature/Admin/AdminOperationalFeaturesTest.php`
- `tests/Feature/Admin/AdminOverviewApiTest.php`

Jalankan:

```bash
php artisan test
```
