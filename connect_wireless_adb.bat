@echo off
title P.A.D.I. - Wireless ADB & API Reverse Connection
color 0A

echo ======================================================================
echo           P.A.D.I. SMART FARMING - WIRELESS ADB CONNECTOR
echo               Koneksi Android Tanpa Kabel (Over Wi-Fi)
echo ======================================================================
echo.

:: 1. Cek ketersediaan perintah adb
where adb >nul 2>nul
if %errorlevel% neq 0 (
    echo [PERINGATAN] Command 'adb' tidak ditemukan di PATH sistem.
    echo Mencari di folder Android SDK standar...
    if exist "%LOCALAPPDATA%\Android\Sdk\platform-tools\adb.exe" (
        set "PATH=%PATH%;%LOCALAPPDATA%\Android\Sdk\platform-tools"
        echo [OK] Menemukan adb di %LOCALAPPDATA%\Android\Sdk\platform-tools
    ) else (
        echo [ERROR] adb.exe tidak ditemukan. Pastikan Android SDK Platform-Tools terpasang.
        echo.
        pause
        exit /b 1
    )
)

echo [INFO] IP Laptop Anda di jaringan Wi-Fi saat ini:
powershell -Command "Get-NetIPAddress -InterfaceAlias 'Wi-Fi*' -AddressFamily IPv4 | Select-Object -ExpandProperty IPAddress" 2>nul
echo.
echo Pastikan HP Android dan Laptop terhubung ke jaringan Wi-Fi yang SAMA.
echo ======================================================================
echo Pilih Metode Koneksi:
echo [1] Android 11+ (Menggunakan Wireless Debugging & Pairing Code)
echo [2] Android 10 kebawah / Sudah pernah colok kabel sekali (adb tcpip 5555)
echo [3] Jalankan ADB Reverse Port Forwarding saja (jika sudah connect wireless)
echo [4] Keluar
echo ======================================================================
set /p opt="Pilih menu [1-4]: "

if "%opt%"=="1" goto ANDROID_11
if "%opt%"=="2" goto ANDROID_LEGACY
if "%opt%"=="3" goto APPLY_REVERSE
if "%opt%"=="4" exit /b 0
goto MENU_END

:ANDROID_11
echo.
echo ----------------------------------------------------------------------
echo Langkah Android 11+ (Wireless Debugging):
echo 1. Di HP: Buka Pengaturan -^> Opsi Pengembang -^> Aktifkan "Wireless debugging"
echo 2. Ketuk "Wireless debugging" -^> Pilih "Pair device with pairing code"
echo ----------------------------------------------------------------------
set /p pair_ip="Masukkan IP dan Port Pairing dari HP (contoh 192.168.1.15:37891): "
set /p pair_code="Masukkan 6 Digit Kode Pairing: "
echo Mengirim pairing...
adb pair %pair_ip% %pair_code%
echo.
echo Sekarang lihat IP dan Port KONEKSI utama di layar Wireless Debugging HP:
set /p conn_ip="Masukkan IP dan Port Koneksi (contoh 192.168.1.15:42135): "
echo Menghubungkan ke HP...
adb connect %conn_ip%
goto APPLY_REVERSE

:ANDROID_LEGACY
echo.
echo ----------------------------------------------------------------------
echo Langkah Mode TCP/IP (Memerlukan kabel USB tercolok sekejap):
echo 1. Colokkan HP ke Laptop dengan kabel USB.
echo 2. Tekan Enter untuk mengaktifkan mode nirkabel (port 5555).
echo ----------------------------------------------------------------------
pause
echo Mengaktifkan port 5555 pada HP...
adb tcpip 5555
echo [OK] Port 5555 aktif. Cabut kabel USB dari HP sekarang!
echo.
set /p phone_ip="Masukkan IP Wi-Fi HP Anda (lihat di Pengaturan Wi-Fi HP, contoh: 192.168.1.15): "
echo Menghubungkan ke %phone_ip%:5555...
adb connect %phone_ip%:5555
goto APPLY_REVERSE

:APPLY_REVERSE
echo.
echo ----------------------------------------------------------------------
echo Menerapkan ADB Reverse Port Forwarding ke HP...
echo ----------------------------------------------------------------------
adb reverse tcp:8000 tcp:8000
adb reverse tcp:8003 tcp:8003
adb reverse tcp:8080 tcp:8080

echo.
echo [STATUS PERANGKAT TERHUBUNG]
adb devices
echo.
echo ======================================================================
echo [BERHASIL!] HP Anda sekarang dapat mengakses API melalui 2 jalur:
echo  1. Jalur Localhost Reverse : http://127.0.0.1:8000 (Backend)
echo                             : http://127.0.0.1:8003 (AI Service)
echo                             : ws://127.0.0.1:8080   (Reverb WebSocket)
echo  2. Jalur IP Wi-Fi Langsung : http://192.168.1.7:8000 (Backend)
echo                             : http://192.168.1.7:8003 (AI Service)
echo                             : ws://192.168.1.7:8080   (Reverb WebSocket)
echo ======================================================================
echo.
echo Silakan buka aplikasi P.A.D.I di HP Anda atau tekan tombol 'r' pada
echo terminal flutter run untuk Hot Reload.
echo.
pause
exit /b 0

:MENU_END
