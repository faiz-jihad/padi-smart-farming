[CmdletBinding()]
param(
    [switch]$BackendOnly,
    [switch]$IncludeAi,
    [switch]$IncludeReverb,
    [switch]$SkipWeb
)

$ErrorActionPreference = 'Stop'
$RootDir = Split-Path -Parent $MyInvocation.MyCommand.Path

function Write-Info {
    param([string]$Message)

    Write-Host $Message -ForegroundColor Cyan
}

function Resolve-Cloudflared {
    $command = Get-Command cloudflared -ErrorAction SilentlyContinue

    if ($null -eq $command) {
        throw @"
cloudflared belum terpasang atau belum ada di PATH.

Install cepat di Windows:
  winget install --id Cloudflare.cloudflared

Setelah itu buka terminal baru dan jalankan ulang:
  .\run_cloudflare_tunnels.bat
"@
    }

    return $command.Source
}

function Test-LocalPort {
    param([int]$Port)

    $client = New-Object System.Net.Sockets.TcpClient

    try {
        $asyncResult = $client.BeginConnect('127.0.0.1', $Port, $null, $null)
        $connected = $asyncResult.AsyncWaitHandle.WaitOne(700, $false)

        if ($connected) {
            $client.EndConnect($asyncResult)
        }

        return $connected
    }
    catch {
        return $false
    }
    finally {
        $client.Close()
    }
}

function Start-PadiTunnel {
    param(
        [string]$Name,
        [string]$LocalUrl,
        [int]$Port,
        [string]$CloudflaredPath
    )

    if (-not (Test-LocalPort -Port $Port)) {
        Write-Warning "$Name belum terdeteksi di port $Port. Jalankan service-nya dulu, atau biarkan tunnel window menunggu lalu refresh setelah service hidup."
    }

    $escapedPath = $CloudflaredPath.Replace("'", "''")
    $escapedUrl = $LocalUrl.Replace("'", "''")
    $escapedName = $Name.Replace("'", "''")

    $command = @"
`$Host.UI.RawUI.WindowTitle = 'P.A.D.I. Tunnel - $escapedName'
Write-Host ''
Write-Host 'P.A.D.I. Tunnel - $escapedName' -ForegroundColor Green
Write-Host 'Local target: $escapedUrl'
Write-Host 'Cari URL https://*.trycloudflare.com pada output di bawah ini.'
Write-Host 'Tutup window ini untuk mematikan tunnel.'
Write-Host ''
& '$escapedPath' tunnel --url '$escapedUrl'
"@

    Start-Process -FilePath 'powershell.exe' `
        -WorkingDirectory $RootDir `
        -ArgumentList @('-NoExit', '-ExecutionPolicy', 'Bypass', '-Command', $command)
}

$cloudflaredPath = Resolve-Cloudflared

$targets = @(
    [pscustomobject]@{
        Name = 'Backend API'
        Url = 'http://localhost:8000'
        Port = 8000
    }
)

if (-not $BackendOnly) {
    if (-not $SkipWeb) {
        $targets += [pscustomobject]@{
            Name = 'Web Landing'
            Url = 'http://localhost:5173'
            Port = 5173
        }
    }

    if ($IncludeAi) {
        $targets += [pscustomobject]@{
            Name = 'AI Service'
            Url = 'http://localhost:8003'
            Port = 8003
        }
    }

    if ($IncludeReverb) {
        $targets += [pscustomobject]@{
            Name = 'Reverb WebSocket'
            Url = 'http://localhost:8080'
            Port = 8080
        }
    }
}

Write-Host '======================================================================' -ForegroundColor Green
Write-Host '             P.A.D.I. Cloudflare Quick Tunnel Runner' -ForegroundColor Green
Write-Host '======================================================================' -ForegroundColor Green
Write-Host ''
Write-Info "cloudflared: $cloudflaredPath"
Write-Host ''

foreach ($target in $targets) {
    Write-Info ("Membuka tunnel {0} -> {1}" -f $target.Name, $target.Url)
    Start-PadiTunnel `
        -Name $target.Name `
        -LocalUrl $target.Url `
        -Port $target.Port `
        -CloudflaredPath $cloudflaredPath
}

Write-Host ''
Write-Host 'Setelah window tunnel terbuka, copy URL https://*.trycloudflare.com dari masing-masing output.' -ForegroundColor Yellow
Write-Host ''
Write-Host 'Untuk Flutter mobile, pakai URL Backend API dan tambahkan /api/v1:' -ForegroundColor Yellow
Write-Host '  flutter run --dart-define=API_BASE_URL=https://nama-tunnel.trycloudflare.com/api/v1'
Write-Host ''
Write-Host 'Opsi:' -ForegroundColor Yellow
Write-Host '  .\run_cloudflare_tunnels.bat -BackendOnly'
Write-Host '  .\run_cloudflare_tunnels.bat -IncludeAi'
Write-Host '  .\run_cloudflare_tunnels.bat -IncludeReverb'
Write-Host '  .\run_cloudflare_tunnels.bat -SkipWeb'
Write-Host ''
Write-Host 'Catatan: quick tunnel bersifat publik dan URL berubah setiap kali dijalankan.' -ForegroundColor Yellow
