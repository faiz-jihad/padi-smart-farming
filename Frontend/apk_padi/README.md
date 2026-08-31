# apk_padi

A new Flutter project.

## Backend connection

Run the Laravel API from `Backend/backend-apk-padi`:

```powershell
php artisan serve --host=0.0.0.0 --port=8000
```

For Android emulator, the default app config uses `10.0.2.2:8000`.

Do not point the Flutter API config to port `8001`. Port `8001` is reserved for
the Python AI service, while login, auth, farms, and disease scan API calls must
go through Laravel on port `8000`.

For a physical Android device, pass your computer LAN IP:

```powershell
flutter run --dart-define=API_BASE_URL=http://YOUR_PC_IP:8000/api/v1
```

You can also provide multiple fallback hosts:

```powershell
flutter run --dart-define=API_HOSTS=YOUR_PC_IP,10.0.2.2,127.0.0.1
```

## Getting Started

This project is a starting point for a Flutter application.

A few resources to get you started if this is your first Flutter project:

- [Learn Flutter](https://docs.flutter.dev/get-started/learn-flutter)
- [Write your first Flutter app](https://docs.flutter.dev/get-started/codelab)
- [Flutter learning resources](https://docs.flutter.dev/reference/learning-resources)

For help getting started with Flutter development, view the
[online documentation](https://docs.flutter.dev/), which offers tutorials,
samples, guidance on mobile development, and a full API reference.
