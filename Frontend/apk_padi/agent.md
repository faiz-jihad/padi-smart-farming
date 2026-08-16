# Frontend Agent Guide

Dokumen ini menjadi aturan kerja untuk agent yang mengubah Flutter app P.A.D.I.

## Prinsip

- Ikuti struktur feature-based yang sudah ada.
- Jangan menaruh request HTTP langsung di widget.
- Widget fokus ke tampilan dan state kecil.
- Controller/Riverpod mengatur state dan memanggil repository/service.
- Semua endpoint harus lewat `core/network/api_client.dart`.
- Token auth harus lewat `core/storage/token_storage.dart`.
- Route protected harus lewat `core/router/app_router.dart`.

## Struktur Yang Dipakai

- `lib/core`: config, router, network, storage, helper, error.
- `lib/features/auth`: login, register, profile, password, home.
- `lib/features/admin`: admin overview untuk user role admin.

## Pola Implementasi

1. Model data di `features/<feature>/data/models`.
2. Service API di `features/<feature>/data/services`.
3. Repository contract di `features/<feature>/domain/repositories`.
4. Repository implementation di `features/<feature>/data/repositories`.
5. Controller state di `features/<feature>/presentation/controllers`.
6. Screen/widget di `features/<feature>/presentation`.

## Larangan

- Jangan hardcode base URL di screen.
- Jangan simpan token di memory biasa.
- Jangan tampilkan menu admin untuk non-admin.
- Jangan membuat UI statis untuk data yang sudah punya endpoint.
- Jangan swallow error tanpa feedback ke user.
