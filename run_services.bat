@echo off
title P.A.D.I. Multi-Service Runner
color 0A

echo ======================================================================
echo             P.A.D.I. (Predictive Agriculture System)
echo           Menjalankan 3 Service Secara Bersamaan
echo ======================================================================
echo.

:: 1. Jalankan AI Microservice (Port 8003)
echo [1/3] Menjalankan AI Microservice (FastAPI - Port 8003)...
cd /d "D:\Hackathon KMIPN\ai-service"
if exist ".venv\Scripts\python.exe" (
    start "P.A.D.I. AI Service (FastAPI)" cmd /k "cd /d D:\Hackathon KMIPN\ai-service && .venv\Scripts\python.exe -m uvicorn app.main:app --host 0.0.0.0 --port 8003"
    echo [OK] AI Microservice diluncurkan dengan Python VirtualEnv di port 8003.
) else (
    docker compose up -d
    echo [OK] AI Microservice diluncurkan dengan Docker di port 8003.
)
echo.

:: 2. Jalankan Backend Laravel di Window Terpisah
echo [2/3] Membuka Backend Laravel (Port 8000) di window baru...
start "P.A.D.I. Backend (Laravel)" cmd /k "cd /d D:\Hackathon KMIPN\Backend\backend-apk-padi && php artisan serve --host=0.0.0.0 --port=8000"
echo [OK] Backend Laravel berjalan di http://0.0.0.0:8000.
echo.

:: 3. Jalankan Frontend Flutter
echo [3/3] Membuka Frontend Flutter di window baru...
start "P.A.D.I. Frontend (Flutter)" cmd /k "cd /d D:\Hackathon KMIPN\Frontend\apk_padi && flutter run"
echo [OK] Flutter build & run diluncurkan.
echo.

echo ======================================================================
echo Semua 3 service telah dijalankan:
echo 1. AI Service   : http://127.0.0.1:8003
echo 2. Backend API  : http://0.0.0.0:8000 (LAN: 192.168.100.10:8000)
echo 3. Frontend App : Flutter Mobile (Android/Emulator)
echo ======================================================================
pause
