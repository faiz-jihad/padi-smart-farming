@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/weather.css') }}">

<div class="weather-page">
    {{-- Breadcrumb --}}
    <nav class="weather-breadcrumb" aria-label="Breadcrumb">
        <span>Admin</span>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <a href="{{ route('admin.weather.index') }}" style="color:#64748b; text-decoration:none;">Manajemen Cuaca</a>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="weather-breadcrumb-current">Pengaturan API & Integrasi</span>
    </nav>

    {{-- Page Header --}}
    <div class="weather-header">
        <div class="weather-header-content">
            <h1 class="weather-title">Pengaturan API Cuaca & Integrasi</h1>
            <p class="weather-description">Konfigurasi penyedia layanan cuaca, kunci API eksternal, pengujian koneksi, serta manajemen cache data.</p>
        </div>

        <div class="weather-header-actions">
            <a href="{{ route('admin.weather.index') }}" class="btn-weather-action">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if (session('status'))
        <div class="weather-alert weather-alert-success" id="alert-status">
            <span>{{ session('status') }}</span>
            <button type="button" class="weather-alert-close" onclick="document.getElementById('alert-status').remove()">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="weather-alert weather-alert-danger" id="alert-error">
            <span>{{ session('error') }}</span>
            <button type="button" class="weather-alert-close" onclick="document.getElementById('alert-error').remove()">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="weather-alert weather-alert-danger" id="alert-validation">
            <div>
                <strong>Validasi Gagal:</strong>
                <ul style="margin: 6px 0 0 0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="weather-alert-close" onclick="document.getElementById('alert-validation').remove()">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- Main Grid --}}
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px;">
        {{-- Section 1: Konfigurasi Provider --}}
        <section class="data-card" style="margin-bottom:0;">
            <div class="data-header">
                <div>
                    <h2>Penyedia Layanan API</h2>
                    <p>Pilih provider data cuaca eksternal dan API key</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.weather.settings.update') }}" style="padding: 24px;">
                @csrf
                @method('PATCH')

                <div style="margin-bottom: 20px;">
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="weather_provider">Pilih Provider Cuaca</label>
                    <select name="weather_provider" id="weather_provider" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff; outline:none;" required>
                        <option value="openweathermap" @selected($provider === 'openweathermap')>
                            OpenWeatherMap (Layanan Cuaca Umum)
                        </option>
                        <option value="agromonitoring" @selected($provider === 'agromonitoring')>
                            AgroMonitoring (Cuaca Pertanian & Sensor Kelembaban Tanah)
                        </option>
                    </select>
                    <p style="font-size:12px; color:#64748b; margin:6px 0 0 0;">Provider yang aktif menentukan sumber data observasi cuaca dan sensor tanah.</p>
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="weather_api_key">API Key Khusus</label>
                    <input type="password" name="weather_api_key" id="weather_api_key" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff; outline:none; box-sizing:border-box;" placeholder="Masukkan API key baru (kosongkan jika tidak diubah)">
                    <p style="font-size:12px; color:#64748b; margin:6px 0 0 0; line-height:1.5;">
                        Status Kunci API saat ini: <strong style="color:#0f172a;">{{ $weatherApiKey }}</strong><br>
                        Dapatkan API key gratis melalui portal resmi <a href="https://openweathermap.org/api" target="_blank" rel="noopener" style="color:#1b5e20; font-weight:600;">OpenWeatherMap</a> atau <a href="https://home.agromonitoring.com/users/api-keys" target="_blank" rel="noopener" style="color:#1b5e20; font-weight:600;">AgroMonitoring</a>.
                    </p>
                </div>

                <button type="submit" class="btn-weather-action btn-weather-primary" style="width:100%; justify-content:center; padding:12px;">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Simpan Konfigurasi</span>
                </button>
            </form>
        </section>

        {{-- Section 2: Diagnostik & Maintenance --}}
        <div style="display:flex; flex-direction:column; gap:24px;">
            <section class="data-card" style="margin-bottom:0;">
                <div class="data-header">
                    <div>
                        <h2>Uji Koneksi API</h2>
                        <p>Verifikasi sambungan ke server provider cuaca</p>
                    </div>
                </div>

                <div style="padding: 24px;">
                    <p style="font-size:13px; color:#64748b; margin:0 0 16px 0; line-height:1.5;">
                        Lakukan pengujian sambungan HTTP ke API provider aktif untuk memastikan kunci API dan koneksi internet berfungsi normal.
                    </p>

                    <form method="POST" action="{{ route('admin.weather.test-connection') }}">
                        @csrf
                        <button type="submit" class="btn-weather-action" style="width:100%; justify-content:center; padding:12px;">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span>Tes Sambungan API Sekarang</span>
                        </button>
                    </form>
                </div>
            </section>

            <section class="data-card" style="margin-bottom:0;">
                <div class="data-header">
                    <div>
                        <h2>Manajemen Cache Data</h2>
                        <p>Kosongkan memori penyimpanan sementara data cuaca</p>
                    </div>
                </div>

                <div style="padding: 24px;">
                    <p style="font-size:13px; color:#64748b; margin:0 0 16px 0; line-height:1.5;">
                        Membersihkan cache cuaca akan memaksa sistem mengambil snapshot data baru dari provider pada permintaan berikutnya.
                    </p>

                    <form method="POST" action="{{ route('admin.weather.clear-cache') }}">
                        @csrf
                        <button type="submit" class="btn-weather-action" style="width:100%; justify-content:center; padding:12px; color:#dc2626; border-color:#fca5a5;" onclick="return confirm('Apakah Anda yakin ingin menghapus seluruh cache data cuaca?')">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span>Bersihkan Seluruh Cache Data</span>
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </div>

    {{-- Section 3: Dokumentasi API V1 --}}
    <section class="data-card">
        <div class="data-header">
            <div>
                <h2>Dokumentasi & Referensi API V1 Cuaca</h2>
                <p>Spesifikasi endpoint REST API Cuaca untuk aplikasi mobile dan integrasi IoT</p>
            </div>
        </div>

        <div style="padding: 24px; display:flex; flex-direction:column; gap:20px;">
            <div style="background:#f8fafc; border-left:4px solid #1b5e20; padding:16px 20px; border-radius:8px;">
                <span style="font-weight:700; font-size:14px; color:#0f172a;">1. Endpoint Cuaca Lahan Real-Time</span>
                <pre style="background:#0f172a; color:#38bdf8; padding:12px 16px; border-radius:8px; font-size:12px; margin:8px 0 0 0; overflow-x:auto;">POST /api/v1/weather/current
{
    "farm_id": 1,
    "units": "metric",
    "lang": "id",
    "force_refresh": false
}</pre>
            </div>

            <div style="background:#f8fafc; border-left:4px solid #1b5e20; padding:16px 20px; border-radius:8px;">
                <span style="font-weight:700; font-size:14px; color:#0f172a;">2. Endpoint Prakiraan Cuaca 5 Hari</span>
                <pre style="background:#0f172a; color:#38bdf8; padding:12px 16px; border-radius:8px; font-size:12px; margin:8px 0 0 0; overflow-x:auto;">POST /api/v1/weather/forecast
{
    "farm_id": 1,
    "units": "metric",
    "lang": "id"
}</pre>
            </div>

            <div style="background:#f8fafc; border-left:4px solid #1b5e20; padding:16px 20px; border-radius:8px;">
                <span style="font-weight:700; font-size:14px; color:#0f172a;">3. Endpoint Riwayat Snapshot Cuaca</span>
                <pre style="background:#0f172a; color:#38bdf8; padding:12px 16px; border-radius:8px; font-size:12px; margin:8px 0 0 0; overflow-x:auto;">GET /api/v1/weather/history?farm_id=1&limit=30</pre>
            </div>
        </div>
    </section>
</div>
@endsection
