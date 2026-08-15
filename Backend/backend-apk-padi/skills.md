# Backend Skills

Daftar kemampuan teknis yang relevan untuk mengerjakan backend.

## Laravel

- Routing API dan web.
- Middleware auth, role, dan account status.
- FormRequest validation.
- Eloquent model, relation, query builder.
- Resource response untuk API.
- Feature test dengan PHPUnit.
- Laravel Pint untuk formatting.

## Admin

- Admin login memakai session guard web.
- Admin API memakai Sanctum token dan middleware role admin.
- Audit log untuk aksi penting.
- Broadcast dan notifikasi realtime.
- Blade admin dengan data real dari database.

## Realtime

- Laravel Reverb sebagai WebSocket server.
- Laravel Echo + pusher-js di asset Vite.
- Private channel authorization di `routes/channels.php`.

## Commands

```bash
composer install
php artisan migrate
php artisan db:seed
php artisan test
vendor/bin/pint
npm install
npm run build
php artisan serve
php artisan reverb:start
npm run dev
```

## Review Checklist

- Controller tetap tipis.
- Logic baru masuk service/action.
- Validasi masuk FormRequest.
- Tidak ada dummy data di Blade admin.
- Test feature ditambah untuk flow baru.
- `php artisan test` lulus.
- `npm run build` lulus bila mengubah asset.
