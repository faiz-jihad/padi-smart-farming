@echo off
title P.A.D.I. Multi-Service Runner
color 0A

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
cd /d "D:\Hackathon KMIPN\ai-service"
if exist ".venv\Scripts\python.exe" (
    start "P.A.D.I. AI Service (FastAPI)" cmd /k "cd /d D:\Hackathon KMIPN\ai-service && .venv\Scripts\python.exe -m uvicorn app.main:app --host 0.0.0.0 --port 8003"
    echo [OK] AI Microservice diluncurkan dengan Python VirtualEnv di port 8003.
) else (
    docker compose up -d
    echo [OK] AI Microservice diluncurkan dengan Docker di port 8003.
)
echo.

:: 2. Jalankan Laravel Reverb WebSocket Server (Port 8080)
echo [2/4] Menjalankan WebSocket Server Laravel Reverb (Port 8080)...
start "P.A.D.I. WebSocket (Reverb)" cmd /k "cd /d D:\Hackathon KMIPN\Backend\backend-apk-padi && php artisan reverb:start --host=0.0.0.0 --port=8080 --debug"
echo [OK] Reverb WebSocket Server berjalan di ws://0.0.0.0:8080.
echo.

:: 3. Jalankan Backend Laravel di Window Terpisah (Port 8000)
echo [3/4] Membuka Backend Laravel (Port 8000) di window baru...
start "P.A.D.I. Backend (Laravel)" cmd /k "cd /d D:\Hackathon KMIPN\Backend\backend-apk-padi && php artisan serve --host=0.0.0.0 --port=8000"
echo [OK] Backend Laravel berjalan di http://0.0.0.0:8000.
echo.

:: 4. Jalankan Frontend Flutter
echo [4/5] Membuka Frontend Flutter di window baru...
start "P.A.D.I. Frontend (Flutter)" cmd /k "cd /d D:\Hackathon KMIPN\Frontend\apk_padi && flutter run"
echo [OK] Flutter build & run diluncurkan.
echo.

:: 5. Jalankan Web Scrollytelling P.A.D.I. (Port 5173)
echo [5/5] Membuka Web Scrollytelling P.A.D.I. (Port 5173)...
start "P.A.D.I. Web Scrollytelling (Vite)" cmd /k "cd /d D:\Hackathon KMIPN\padi-web && npm run dev"
echo [OK] Web Scrollytelling berjalan di http://localhost:5173.
echo.

echo ======================================================================
echo Semua 5 service telah dijalankan:
echo 1. AI Service       : http://127.0.0.1:8003 (Swagger: /docs)
echo 2. WebSocket Reverb : ws://0.0.0.0:8080
echo 3. Backend API      : http://0.0.0.0:8000 (LAN: 192.168.100.10:8000)
echo 4. Frontend App     : Flutter Mobile (Android/Emulator)
echo 5. Web Welcome Page : http://localhost:5173 (Scrollytelling & Download APK)
echo ======================================================================
pause
