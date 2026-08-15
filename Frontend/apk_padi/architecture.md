# Frontend Architecture

Frontend memakai Flutter, Riverpod, Dio, GoRouter, dan Flutter Secure Storage.

## Layer

### Core

- `core/config/app_config.dart`: konfigurasi aplikasi.
- `core/network/api_client.dart`: Dio client, auth header, API error handling.
- `core/storage/token_storage.dart`: penyimpanan token aman.
- `core/router/app_router.dart`: route dan redirect auth/admin.
- `core/errors` dan `core/helpers`: exception dan helper response.

### Feature

Setiap feature memakai pola:

- `data/models`: DTO/model dari API.
- `data/services`: HTTP call.
- `data/repositories`: implementasi repository.
- `domain/entities`: entity app.
- `domain/repositories`: contract.
- `presentation/controllers`: state dan action.
- `presentation/screens/widgets`: UI.

## Auth Flow

1. User login/register lewat auth API.
2. Token disimpan di secure storage.
3. Router membaca auth state.
4. Route protected diarahkan ke home.
5. Role admin dapat membuka route admin.

## Admin Flow

Admin FE memakai endpoint backend:

```text
GET/PATCH/POST/DELETE /api/v1/admin/{resource?}/{id?}
```

Data utama:

- summary,
- users,
- broadcasts,
- audit logs.

## Testing

Gunakan:

```bash
flutter analyze
flutter test
```

Tambahkan test untuk parsing model, repository, dan controller state bila fitur menyentuh flow penting.
