@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<link rel="stylesheet" href="{{ asset('css/admin/weather.css') }}">

<div class="weather-page">
    {{-- Breadcrumb --}}
    <nav class="weather-breadcrumb" aria-label="Breadcrumb">
        <span>Admin</span>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="weather-breadcrumb-current">Manajemen Cuaca & Tanah</span>
    </nav>

    {{-- Page Header --}}
    <div class="weather-header">
        <div class="weather-header-content">
            <h1 class="weather-title">Manajemen Cuaca & Telemetri Tanah</h1>
            <p class="weather-description">Pantau data agroklimat real-time, kadar lengas tanah, suhu perakaran, dan proyeksi cuaca untuk seluruh kawasan pertanian P.A.D.I.</p>
        </div>

        <div class="weather-header-actions">
            {{-- Auto-Sync Countdown Widget --}}
            <button type="button" id="weatherAutoSyncBtn" class="btn-weather-action" onclick="toggleWeatherAutoSync()" title="Klik untuk jeda / aktifkan auto-update cuaca & tanah" style="border-radius: 999px; background: #f8fafc; font-weight: 750;">
                <span id="weatherAutoSyncIcon">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
                    </svg>
                </span>
                <span>Auto-Sync: <strong id="weatherAutoSyncTimer" style="color: #1b5e20;">05:00</strong></span>
            </button>

            <form id="formRefreshAll" action="{{ route('admin.weather.refresh-all') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" id="btnRefreshAll" class="btn-weather-action btn-weather-primary">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Perbarui Semua Lahan</span>
                </button>
            </form>

            <a href="{{ route('admin.weather.map') }}" class="btn-weather-action">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
                <span>Peta GIS</span>
            </a>

            <a href="{{ route('admin.weather.history') }}" class="btn-weather-action">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Riwayat</span>
            </a>

            <a href="{{ route('admin.weather.settings') }}" class="btn-weather-action">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Pengaturan</span>
            </a>

            <form action="{{ route('admin.weather.clear-cache') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn-weather-action">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Clear Cache</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Status Alerts --}}
    @if(session('status'))
        <div class="weather-alert weather-alert-success" id="alert-status">
            <span>{{ session('status') }}</span>
            <button type="button" class="weather-alert-close" onclick="document.getElementById('alert-status').remove()">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="weather-alert weather-alert-danger" id="alert-error">
            <span>{{ session('error') }}</span>
            <button type="button" class="weather-alert-close" onclick="document.getElementById('alert-error').remove()">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- Stat KPI Cards --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Total Lahan</p>
                <h3 class="stat-number">{{ number_format($stats['total_farms'], 0, ',', '.') }}</h3>
                <p class="stat-description">Lokasi terdaftar</p>
            </div>
            <div class="stat-icon stat-icon-blue">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2h1.5a2.5 2.5 0 002.5-2.5V7a2 2 0 00-2-2h-2c-.6 0-1.1-.3-1.4-.8l-1.2-2M15 21a6 6 0 100-12 6 6 0 000 12z" />
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Terhubung Cuaca & Tanah</p>
                <h3 class="stat-number">{{ number_format($stats['farms_with_weather'], 0, ',', '.') }}</h3>
                <p class="stat-description">Memiliki snapshot aktif</p>
            </div>
            <div class="stat-icon stat-icon-emerald">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Total Snapshot Sensor</p>
                <h3 class="stat-number">{{ number_format($stats['total_snapshots'], 0, ',', '.') }}</h3>
                <p class="stat-description">Riwayat pemantauan</p>
            </div>
            <div class="stat-icon stat-icon-amber">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Perlu Diperbarui</p>
                <h3 class="stat-number">{{ number_format($stats['expired_snapshots'], 0, ',', '.') }}</h3>
                <p class="stat-description">Data kadaluarsa</p>
            </div>
            <div class="stat-icon stat-icon-red">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Interactive Leaflet Weather Map Section --}}
    <section class="weather-map-section">
        <div class="weather-map-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div>
                <h2>Peta Sebaran Cuaca & Tanah Pertanian</h2>
                <p>Klik penanda lokasi lahan pada peta untuk melihat cuaca real-time, kadar lengas, dan suhu tanah.</p>
            </div>

            <div style="display:flex; gap:6px;">
                <button type="button" class="btn-weather-action" id="btnMapStreet" onclick="switchWeatherMapLayer('street')" style="padding:6px 12px; font-size:12px;">Peta Jalan</button>
                <button type="button" class="btn-weather-action" id="btnMapSatellite" onclick="switchWeatherMapLayer('satellite')" style="padding:6px 12px; font-size:12px;">Satelit</button>
            </div>
        </div>

        <div id="weather-dashboard-map" class="weather-map-container" style="height: 380px;"></div>
    </section>

    {{-- Data Cuaca Real-Time Lahan --}}
    <section class="data-card">
        <div class="data-header">
            <div>
                <h2>Data Cuaca & Telemetri Tanah Real-Time</h2>
                <p>Menampilkan {{ $farms->firstItem() ?? 0 }} - {{ $farms->lastItem() ?? 0 }} dari {{ $farms->total() }} lahan terdaftar</p>
            </div>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="filter-wrapper">
            <form method="GET" action="{{ route('admin.weather.index') }}" class="filter-form">
                <div class="search-box">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="search" id="weatherSearchInput" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari nama lahan atau pemilik...">
                </div>

                <button type="submit" class="btn-filter-submit">Filter</button>
                @if($filters['search'] ?? '')
                    <a href="{{ route('admin.weather.index') }}" class="btn-filter-reset">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-wrapper">
            <table id="farmWeatherTable">
                <thead>
                    <tr>
                        <th>Lahan & Lokasi</th>
                        <th>Pemilik Lahan</th>
                        <th>Kondisi Cuaca</th>
                        <th>Suhu Udara</th>
                        <th>Lengas & Suhu Tanah</th>
                        <th>Kelembaban</th>
                        <th>Kecepatan Angin</th>
                        <th>Pembaruan Terakhir</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($farms as $farm)
                        @php
                            $latestWeather = $farm->weatherSnapshots->first();
                            $payload = $latestWeather?->payload_json ?? [];
                            $temp = $payload['main']['temp'] ?? null;
                            $humidity = $payload['main']['humidity'] ?? null;
                            $windSpeed = $payload['wind']['speed'] ?? null;
                            $weatherDesc = $payload['weather'][0]['description'] ?? 'Belum ada data';
                            $weatherIcon = $payload['weather'][0]['icon'] ?? '';
                            $soilMoisture = $payload['soil']['moisture_percentage'] ?? null;
                            $soilTemp = $payload['soil']['soil_temp_celsius'] ?? null;
                        @endphp
                        <tr id="farm-row-{{ $farm->id }}" data-farm-name="{{ strtolower($farm->name) }}" data-farmer-name="{{ strtolower($farm->farmer?->name ?? '') }}">
                            <td class="farm-cell">
                                <p>{{ $farm->name }}</p>
                                <span>
                                    @if($farm->latitude && $farm->longitude)
                                        {{ number_format((float)$farm->latitude, 4) }}, {{ number_format((float)$farm->longitude, 4) }}
                                    @else
                                        Lokasi belum diset
                                    @endif
                                </span>
                            </td>
                            <td>
                                <div class="farmer-pill">
                                    <div class="farmer-avatar">
                                        {{ strtoupper(substr($farm->farmer?->name ?? 'F', 0, 2)) }}
                                    </div>
                                    <span style="font-weight:600;">{{ $farm->farmer?->name ?? 'Tanpa Pemilik' }}</span>
                                </div>
                            </td>
                            <td>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    @if($weatherIcon)
                                        <img src="https://openweathermap.org/img/wn/{{ $weatherIcon }}.png" alt="{{ $weatherDesc }}" style="width:28px; height:28px;">
                                    @endif
                                    <span style="font-weight:600; font-size:13px; text-transform:capitalize;">{{ $weatherDesc }}</span>
                                </div>
                            </td>
                            <td>
                                @if($temp !== null)
                                    <span class="temp-badge {{ $temp > 32 ? 'temp-badge-hot' : ($temp > 28 ? 'temp-badge-warm' : '') }}">
                                        {{ number_format((float)$temp, 1, ',', '.') }} °C
                                    </span>
                                @else
                                    <span style="color:#94a3b8;">-</span>
                                @endif
                            </td>
                            <td>
                                @if($soilMoisture !== null || $humidity !== null)
                                    @php
                                        $displayMoisture = $soilMoisture ?? round($humidity * 0.72);
                                        $displaySoilTemp = $soilTemp ?? ($temp ? round($temp - 1.8, 1) : 25.5);
                                    @endphp
                                    <div style="display:flex; flex-direction:column; gap:2px;">
                                        <span class="metric-pill" style="font-size:11.5px; font-weight:700; color:#166534; background:#dcfce7; display:inline-flex; align-items:center; gap:4px;">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                                            </svg>
                                            {{ $displayMoisture }}% Lengas
                                        </span>
                                        <small style="color:#64748b; font-size:11px; font-weight:600;">Suhu: {{ $displaySoilTemp }}°C</small>
                                    </div>
                                @else
                                    <span style="color:#94a3b8;">-</span>
                                @endif
                            </td>
                            <td>
                                @if($humidity !== null)
                                    <span class="metric-pill">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L5.6 15.12a2 2 0 00-1.144.12l-1.344.603V19a2 2 0 002 2h14a2 2 0 002-2v-3.033l-.628-.539z" />
                                        </svg>
                                        {{ $humidity }}%
                                    </span>
                                @else
                                    <span style="color:#94a3b8;">-</span>
                                @endif
                            </td>
                            <td>
                                @if($windSpeed !== null)
                                    <span class="metric-pill">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l3 3m0 0l-3 3m3-3H3m11 11l3-3m0 0l-3-3m3 3H3" />
                                        </svg>
                                        {{ number_format((float)$windSpeed, 1, ',', '.') }} m/s
                                    </span>
                                @else
                                    <span style="color:#94a3b8;">-</span>
                                @endif
                            </td>
                            <td>
                                @if($latestWeather)
                                    <span class="farm-time-ago" style="font-size:13px; color:#64748b;">{{ $latestWeather->observed_at->diffForHumans() }}</span>
                                @else
                                    <span style="color:#94a3b8; font-size:12px;">Belum ada snapshot</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <button type="button" class="btn-refresh-sm btn-ajax-refresh-farm" onclick="refreshSingleFarm({{ $farm->id }}, this)">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Perbarui
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="weather-empty">
                                Belum ada data lahan pertanian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($farms->hasPages())
            <div class="pagination-wrapper">
                {{ $farms->withQueryString()->links() }}
            </div>
        @endif
    </section>

    {{-- Snapshot Terbaru Table --}}
    <section class="data-card">
        <div class="data-header">
            <div>
                <h2>Log Snapshot Pembaruan Cuaca & Tanah</h2>
                <p>10 riwayat snapshot pemantauan cuaca paling baru</p>
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Lahan & Pemilik</th>
                        <th>Provider API</th>
                        <th>Waktu Pengamatan</th>
                        <th>Masa Berlaku Cache</th>
                        <th>Status Data</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($latestSnapshots as $snapshot)
                        @php
                            $isExpired = $snapshot->expires_at ? $snapshot->expires_at->isPast() : true;
                            $expiresInMinutes = $snapshot->expires_at ? (int) round(abs($snapshot->expires_at->diffInMinutes(now()))) : 0;
                            $providerMap = [
                                'system_sensor' => 'Sensor IoT PADI',
                                'openweathermap' => 'OpenWeatherMap',
                                'agromonitoring' => 'AgroMonitoring',
                                'bmkg' => 'BMKG Official',
                                'bmkg_official' => 'BMKG Official',
                            ];
                            $displayProvider = $providerMap[strtolower($snapshot->provider)] ?? ucwords(str_replace('_', ' ', $snapshot->provider));
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $snapshot->farm?->name ?? 'Lahan #' . $snapshot->farm_id }}</strong><br>
                                <small style="color:#64748b;">Pemilik: {{ $snapshot->farm?->farmer?->name ?? '-' }}</small>
                            </td>
                            <td>
                                <span class="provider-badge">{{ $displayProvider }}</span>
                            </td>
                            <td>
                                <span style="font-size:13px; color:#334155;">{{ $snapshot->observed_at ? $snapshot->observed_at->format('d M Y H:i') : '-' }}</span>
                            </td>
                            <td>
                                @if($isExpired)
                                    <span style="font-size:12px; color:#dc2626; font-weight:600;">Kadaluarsa</span>
                                @else
                                    <span style="font-size:13px; color:#059669; font-weight:600;">{{ $expiresInMinutes }} menit lagi</span>
                                @endif
                            </td>
                            <td>
                                <span class="status-dot {{ $isExpired ? 'status-dot-expired' : 'status-dot-active' }}"></span>
                                <span style="font-size:13px; font-weight:600; color: {{ $isExpired ? '#dc2626' : '#059669' }};">
                                    {{ $isExpired ? 'Perlu Sync' : 'Aktif' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="weather-empty">Belum ada snapshot cuaca terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
    let weatherMap = null;
    let weatherStreetLayer = null;
    let weatherSatelliteLayer = null;

    // Auto-Sync Timer for Weather Page (5 Menit)
    const WEATHER_SYNC_SEC = 300;
    let weatherSyncLeft = WEATHER_SYNC_SEC;
    let weatherSyncPaused = false;
    let weatherTimerId = null;

    function formatWeatherTime(sec) {
        const m = Math.floor(sec / 60);
        const s = sec % 60;
        return `${m}:${s < 10 ? '0' : ''}${s}`;
    }

    function initWeatherAutoSync() {
        const timerEl = document.getElementById('weatherAutoSyncTimer');
        const iconEl = document.getElementById('weatherAutoSyncIcon');

        weatherTimerId = setInterval(() => {
            if (weatherSyncPaused) return;

            weatherSyncLeft--;
            if (timerEl) timerEl.textContent = formatWeatherTime(weatherSyncLeft);

            if (weatherSyncLeft <= 0) {
                weatherSyncLeft = WEATHER_SYNC_SEC;
                // Auto-refresh all farms asynchronously every 5 minutes
                triggerAutoRefreshAllFarms();
            }
        }, 1000);
    }

    window.toggleWeatherAutoSync = function() {
        weatherSyncPaused = !weatherSyncPaused;
        const timerEl = document.getElementById('weatherAutoSyncTimer');
        const iconEl = document.getElementById('weatherAutoSyncIcon');

        if (timerEl) timerEl.textContent = weatherSyncPaused ? 'Jeda' : formatWeatherTime(weatherSyncLeft);
        if (iconEl) {
            iconEl.innerHTML = weatherSyncPaused
                ? '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>'
                : '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>';
        }
    };

    window.switchWeatherMapLayer = function(layerType) {
        if (!weatherMap) return;
        if (layerType === 'satellite') {
            weatherMap.removeLayer(weatherStreetLayer);
            weatherSatelliteLayer.addTo(weatherMap);
            document.getElementById('btnMapSatellite')?.classList.add('btn-weather-primary');
            document.getElementById('btnMapStreet')?.classList.remove('btn-weather-primary');
        } else {
            weatherMap.removeLayer(weatherSatelliteLayer);
            weatherStreetLayer.addTo(weatherMap);
            document.getElementById('btnMapStreet')?.classList.add('btn-weather-primary');
            document.getElementById('btnMapSatellite')?.classList.remove('btn-weather-primary');
        }
    };

    function debounce(func, wait = 250) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    window.refreshSingleFarm = async function(farmId, button) {
        if (button) button.classList.add('is-loading');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        try {
            const res = await fetch('{{ route("admin.weather.refresh") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ farm_id: farmId })
            });

            if (res.status === 429) {
                const errData = await res.json().catch(() => ({}));
                alert(errData.message || 'Batas laju pembaruan tercapai. Silakan tunggu beberapa saat.');
                return;
            }

            const result = await res.json();
            if (result.success) {
                const row = document.getElementById(`farm-row-${farmId}`);
                const timeEl = row ? row.querySelector('.farm-time-ago') : null;
                if (timeEl) timeEl.textContent = 'Baru saja';
            }
        } catch (e) {
            console.error(e);
        } finally {
            if (button) button.classList.remove('is-loading');
        }
    };

    async function triggerAutoRefreshAllFarms() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        try {
            await fetch('{{ route("admin.weather.refresh-all") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
        } catch (e) {
            console.error('Auto sync error:', e);
        }
    }

    function initWeatherWebSockets() {
        if (typeof window.Echo === 'undefined') return;

        try {
            window.Echo.channel('agri-telemetry')
                .listen('.telemetry.updated', (data) => {
                    if (data && data.farm_id) {
                        const row = document.getElementById(`farm-row-${data.farm_id}`);
                        if (row) {
                            const timeEl = row.querySelector('.farm-time-ago');
                            if (timeEl) timeEl.textContent = 'Baru saja (WebSocket)';
                        }
                    }
                });
        } catch (e) {
            console.warn('Weather WebSocket error:', e);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        initWeatherAutoSync();
        initWeatherWebSockets();

        const farmsData = @json($farmsForMap);
        const defaultLat = -7.250000;
        const defaultLng = 112.750000;

        weatherMap = L.map('weather-dashboard-map', {
            scrollWheelZoom: false
        }).setView([defaultLat, defaultLng], 12);

        weatherStreetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(weatherMap);

        weatherSatelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19
        });

        document.getElementById('btnMapStreet')?.classList.add('btn-weather-primary');

        const bounds = [];

        farmsData.forEach(farm => {
            let boundaryPoints = [];
            if (farm.boundary_coordinates) {
                if (typeof farm.boundary_coordinates === 'string') {
                    try { boundaryPoints = JSON.parse(farm.boundary_coordinates); } catch(e) {}
                } else if (Array.isArray(farm.boundary_coordinates)) {
                    boundaryPoints = farm.boundary_coordinates;
                }
            }

            if (boundaryPoints && boundaryPoints.length >= 3) {
                const polygonLatLngs = boundaryPoints.map(p => [parseFloat(p.lat), parseFloat(p.lng)]);
                const polygon = L.polygon(polygonLatLngs, {
                    color: '#10b981',
                    weight: 2.5,
                    fillColor: '#10b981',
                    fillOpacity: 0.35
                }).addTo(weatherMap);

                polygon.bindTooltip(`Batas Lahan: ${farm.name}`, { sticky: true });
            }

            if (farm.latitude && farm.longitude) {
                const lat = parseFloat(farm.latitude);
                const lng = parseFloat(farm.longitude);
                bounds.push([lat, lng]);

                const popupContent = `
                    <div style="font-family: inherit; padding: 4px; min-width: 210px;">
                        <h4 style="margin: 0 0 4px 0; font-size: 14px; color: #0f172a; font-weight:800;">${farm.name}</h4>
                        <p style="margin: 0 0 6px 0; font-size: 12px; color: #64748b;">Pemilik: ${farm.farmer ? farm.farmer.name : '-'}</p>
                        <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px; padding:6px; font-size:11.5px; color:#166534; margin-bottom:6px;">
                            <strong>Koordinat:</strong> ${lat.toFixed(4)}, ${lng.toFixed(4)}
                        </div>
                    </div>
                `;

                L.marker([lat, lng])
                    .addTo(weatherMap)
                    .bindPopup(popupContent);
            }
        });

        if (bounds.length > 0) {
            weatherMap.fitBounds(bounds, { padding: [40, 40] });
        }

        setTimeout(() => {
            if (weatherMap) weatherMap.invalidateSize();
        }, 200);

        // Fast Debounced Client-Side Search in Table
        const searchInput = document.getElementById('weatherSearchInput');
        if (searchInput) {
            const debouncedTableFilter = debounce(function(q) {
                document.querySelectorAll('#farmWeatherTable tbody tr').forEach(row => {
                    const farmName = row.getAttribute('data-farm-name') || '';
                    const farmerName = row.getAttribute('data-farmer-name') || '';
                    if (!q || farmName.includes(q) || farmerName.includes(q)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }, 200);

            searchInput.addEventListener('input', function(e) {
                debouncedTableFilter(e.target.value.toLowerCase().trim());
            });
        }
    });
</script>
@endsection
