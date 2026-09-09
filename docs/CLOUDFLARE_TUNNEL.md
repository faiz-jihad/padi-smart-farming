# Testing Dengan Cloudflare Tunnel

Gunakan helper ini saat butuh URL publik sementara untuk demo, testing dari HP tanpa USB, atau berbagi akses backend/web ke anggota tim.

## Prasyarat

- Service lokal P.A.D.I. sudah berjalan lewat `run_services.bat` atau manual.
- `cloudflared` tersedia di PATH.

Kalau belum ada:

```powershell
winget install --id Cloudflare.cloudflared
```

## Jalankan Tunnel

Dari root project:

```powershell
.\run_cloudflare_tunnels.bat
```

Default-nya script membuka tunnel untuk:

- Backend API: `http://localhost:8000`
- Web landing: `http://localhost:5173`

Setiap window tunnel akan menampilkan URL seperti:

```text
https://nama-random.trycloudflare.com
```

Quick tunnel ini publik selama window tunnel masih terbuka. Tutup window untuk mematikan akses.

## Flutter Mobile

Copy URL dari window `P.A.D.I. Tunnel - Backend API`, lalu jalankan Flutter dengan `API_BASE_URL`:

```powershell
cd .\Frontend\apk_padi
flutter run --dart-define=API_BASE_URL=https://nama-random.trycloudflare.com/api/v1
```

URL harus memakai `https://` dan path `/api/v1`.

## Opsi Tambahan

Expose backend saja:

```powershell
.\run_cloudflare_tunnels.bat -BackendOnly
```

Expose backend, web, dan AI service:

```powershell
.\run_cloudflare_tunnels.bat -IncludeAi
```

Expose backend, web, dan Reverb WebSocket:

```powershell
.\run_cloudflare_tunnels.bat -IncludeReverb
```

Expose backend saja tanpa web:

```powershell
.\run_cloudflare_tunnels.bat -SkipWeb
```

## Troubleshooting

- Jika muncul warning port belum aktif, jalankan dulu service terkait dari `run_services.bat`.
- Jika URL tunnel berubah, jalankan ulang Flutter dengan `API_BASE_URL` terbaru.
- Jangan gunakan quick tunnel untuk data production atau kredensial sensitif.
