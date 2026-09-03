# apk_padi

A new Flutter project.

## Backend connection

Run the Laravel API from `Backend/backend-apk-padi`:

```powershell
php artisan serve --host=0.0.0.0 --port=8000
```

For Android phones connected by USB, run `adb reverse tcp:8000 tcp:8000`; the
default app config tries `127.0.0.1:8000` first. For Android phones on the same
Wi-Fi, the local dev host is `192.168.100.10:8000`. For Android emulator, the
app also falls back to `10.0.2.2:8000`.

Do not point the Flutter API config to the AI service port. Login, auth, farms,
and disease scan API calls must go through Laravel on port `8000`. In the local
Docker setup, Laravel calls the Python AI service on port `8003` internally.

Check that the backend gateway is Laravel before running the app:

```powershell
curl http://127.0.0.1:8000/api/v1/health
```

The response should include `"service":"laravel-backend"`.

For a physical Android device, pass your computer LAN IP:

```powershell
flutter run --dart-define=API_LAN_HOST=YOUR_PC_IP
```

You can also provide multiple fallback hosts:

```powershell
flutter run --dart-define=API_HOSTS=YOUR_PC_IP,10.0.2.2,127.0.0.1
```

If your local machine is slow while the backend calls the AI service, you can
raise the response timeout:

```powershell
flutter run --dart-define=API_RECEIVE_TIMEOUT_SECONDS=180
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
