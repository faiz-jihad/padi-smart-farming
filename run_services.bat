@echo off
title P.A.D.I. Multi-Service Runner
color 0A

set "ROOT_DIR=%~dp0"
if "%ROOT_DIR:~-1%"=="\" set "ROOT_DIR=%ROOT_DIR:~0,-1%"
set "AI_DIR=%ROOT_DIR%\ai-service"
set "BACKEND_DIR=%ROOT_DIR%\Backend\backend-apk-padi"
set "FRONTEND_DIR=%ROOT_DIR%\Frontend\apk_padi"
set "WEB_DIR=%ROOT_DIR%\padi-web"

for /f "tokens=*" %%i in ('powershell -NoProfile -Command "(Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.IPAddress -notmatch '^127\\.' -and $_.IPAddress -notmatch '^169\\.254\\.' -and $_.IPAddress -notmatch '^192\\.168\\.56\\.' } | Select-Object -First 1 -ExpandProperty IPAddress)" 2^>nul') do set "APP_LAN_HOST=%%i"

if "%APP_LAN_HOST%"=="" set "APP_LAN_HOST=192.168.100.10"

set "FLUTTER_CMD=flutter run --dart-define=API_LAN_HOST=%APP_LAN_HOST%"

echo ======================================================================
echo             P.A.D.I. (Predictive Agriculture System)
echo           Menjalankan 4 Service Secara Bersamaan
echo ======================================================================
echo.

:: 0. Setup ADB Reverse Port Forwarding untuk HP Android Fisik via USB
where adb >nul 2>nul
if %errorlevel% equ 0 (
    adb reverse tcp:8000 tcp:8000 >nul 2>nul
    adb reverse tcp:8003 tcp:8003 >nul 2>nul
    adb reverse tcp:8080 tcp:8080 >nul 2>nul
    echo [OK] ADB reverse aktif: HP fisik via USB dapat mengakses 127.0.0.1:8000 dan 127.0.0.1:8003.
    echo.
)

:: 1. Jalankan AI Microservice (Port 8003)
echo [1/4] Menjalankan AI Microservice (FastAPI - Port 8003)...
cd /d "%AI_DIR%"
if exist ".venv\Scripts\python.exe" (
    start "P.A.D.I. AI Service (FastAPI)" cmd /k "cd /d \"%AI_DIR%\" && .venv\Scripts\python.exe -m uvicorn app.main:app --host 0.0.0.0 --port 8003"
    echo [OK] AI Microservice diluncurkan dengan Python VirtualEnv di port 8003.
) else (
    docker compose up -d
    echo [OK] AI Microservice diluncurkan dengan Docker di port 8003.
)
echo.

:: 2. Jalankan Laravel Reverb WebSocket Server (Port 8080)
echo [2/4] Menjalankan WebSocket Server Laravel Reverb (Port 8080)...
start "P.A.D.I. WebSocket (Reverb)" cmd /k "cd /d \"%BACKEND_DIR%\" && php artisan reverb:start --host=0.0.0.0 --port=8080 --debug"
echo [OK] Reverb WebSocket Server berjalan di ws://0.0.0.0:8080.
echo.

:: 3. Jalankan Backend Laravel di Window Terpisah (Port 8000)
echo [3/4] Membuka Backend Laravel (Port 8000) di window baru...
start "P.A.D.I. Backend (Laravel)" cmd /k "cd /d \"%BACKEND_DIR%\" && php artisan serve --host=0.0.0.0 --port=8000"
echo [OK] Backend Laravel berjalan di http://0.0.0.0:8000.
echo.

if "%APP_LAN_HOST%"=="" (
    echo [WARN] Tidak dapat otomatis mendeteksi IP LAN. Gunakan manual: flutter run --dart-define=API_LAN_HOST=YOUR_PC_IP
) else (
    echo [INFO] Flutter akan menggunakan API_LAN_HOST=%APP_LAN_HOST%
)
echo.

:: 4. Jalankan Frontend Flutter
echo [4/5] Membuka Frontend Flutter di window baru...
start "P.A.D.I. Frontend (Flutter)" cmd /k "cd /d \"%FRONTEND_DIR%\" && %FLUTTER_CMD%"
echo [OK] Flutter build & run diluncurkan dengan API_LAN_HOST=%APP_LAN_HOST%.
echo.

:: 5. Jalankan Web Scrollytelling P.A.D.I. (Port 5173)
echo [5/5] Membuka Web Scrollytelling P.A.D.I. (Port 5173)...
start "P.A.D.I. Web Scrollytelling (Vite)" cmd /k "cd /d \"%WEB_DIR%\" && npm run dev"
echo [OK] Web Scrollytelling berjalan di http://localhost:5173.
echo.

echo ======================================================================
echo Semua 5 service telah dijalankan:
echo 1. AI Service       : http://127.0.0.1:8003 (Swagger: /docs)
echo 2. WebSocket Reverb : ws://0.0.0.0:8080
echo 3. Backend API      : http://0.0.0.0:8000 (LAN: %APP_LAN_HOST%:8000)
echo 4. Frontend App     : Flutter Mobile (Android/Emulator)
echo 5. Web Welcome Page : http://localhost:5173 (Scrollytelling & Download APK)
echo ======================================================================
echo.
echo Untuk testing publik via Cloudflare Tunnel:
echo - Jalankan: run_cloudflare_tunnels.bat
echo - Flutter:  flutter run --dart-define=API_BASE_URL=https://URL-BACKEND.trycloudflare.com/api/v1
echo.
pause
