@echo off
title Laravel Reverb WebSocket Server (Port 8080)
echo ========================================================
echo   Starting P.A.D.I. Laravel Reverb WebSocket Server
echo   Listening on: 0.0.0.0:8080 (Pusher Protocol)
echo ========================================================
echo.

set PHP_BIN=php
if exist "C:\laragon\bin\php\php 8.4\php.exe" (
    set "PHP_BIN=C:\laragon\bin\php\php 8.4\php.exe"
)

cd /d "%~dp0Backend\backend-apk-padi"
"%PHP_BIN%" artisan reverb:start --host=0.0.0.0 --port=8080 --debug

pause
