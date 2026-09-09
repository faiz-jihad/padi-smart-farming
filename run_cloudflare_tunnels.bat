@echo off
title P.A.D.I. Cloudflare Tunnel Runner
color 0B

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0run_cloudflare_tunnels.ps1" %*

echo.
pause
