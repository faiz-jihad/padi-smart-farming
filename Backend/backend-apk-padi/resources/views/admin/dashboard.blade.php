@extends('layouts.admin')

@section('content')

{{-- External Visualization & Map Libraries --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">

@php
    $metricLinks = [
        'users' => route('admin.users.index'),
        'farm' => route('admin.agriculture.index'),
        'warning' => route('admin.disease.index'),
        'market' => route('admin.marketplace.index'),
    ];

    $dashboardDataJson = [
        'liveWeather' => $liveWeather,
        'forecastDays' => $forecastDays,
        'hourlyTelemetry' => $hourlyTelemetry ?? [],
        'monthlyTrends' => $monthlyTrends ?? [],
        'farmsForMap' => $farmsForMap ?? [],
        'disasterThreats' => $disasterThreats,
        'disasterSummary' => $disasterSummary,
    ];
@endphp

<script>
    window.dashboardData = @json($dashboardDataJson);
</script>

<div class="dashboard-page">

    @if(session('status'))
        <div class="dashboard-alert" role="status">
            {{ session('status') }}
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- Dashboard Header & Command Actions Bar                                    --}}
    {{-- ========================================================================= --}}
    <header class="dashboard-header">
        <div>
            <nav class="dashboard-breadcrumb" aria-label="Breadcrumb">
                <span>Admin</span>
                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span>Dashboard</span>
            </nav>

            <h1>Dashboard Operasional P.A.D.I.</h1>
            <p>Pusat kendali agroklimat cerdas, telemetri mikroklimat lahan, dan mitigasi dini pertanian.</p>
        </div>

        <div class="dashboard-header-right">
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <span class="dashboard-live-pill">
                    <span class="dashboard-live-dot"></span>
                    <span>Sensor IoT Aktif</span>
                </span>

                <button type="button" id="autoSyncToggleBtn" class="dashboard-autosync-pill" onclick="toggleAutoSync()" title="Klik untuk jeda / lanjutkan pembaruan otomatis">
                    <span id="autoSyncIcon" class="autosync-icon">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
                        </svg>
                    </span>
                    <span>Auto-Sync: <strong id="autoSyncCountdown">05:00</strong></span>
                </button>

                <div class="dashboard-period" aria-label="Periode data">
                    <span>Hari Ini</span>
                    <strong>{{ now()->translatedFormat('d F Y') }}</strong>
                </div>
            </div>

            <div class="dashboard-header-actions">
                <button type="button" onclick="openQuickBroadcastModal()" class="btn-dash-action btn-dash-primary" title="Kirim broadcast peringatan ke petani">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>
                    </svg>
                    <span>Kirim Peringatan</span>
                </button>

                <button type="button" id="btnSyncDashboard" class="btn-dash-action btn-dash-sync" title="Sinkronkan data sensor via AJAX">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Sinkronkan Data</span>
                </button>

                <button type="button" onclick="openQuickReportModal()" class="btn-dash-action" title="Cetak ringkasan operasional harian">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6z"/>
                    </svg>
                    <span>Ringkasan</span>
                </button>
            </div>
        </div>
    </header>

    {{-- ========================================================================= --}}
    {{-- Key Performance Indicators (KPIs)                                         --}}
    {{-- ========================================================================= --}}
    <section class="dashboard-kpi-grid" aria-label="Ringkasan metrik operasional">
        @foreach($metrics as $metric)
            @include('admin.partials.kpi-card', [
                'metric' => $metric,
                'href' => $metricLinks[$metric['icon']] ?? null,
            ])
        @endforeach
    </section>

    {{-- ========================================================================= --}}
    {{-- Visualisasi Telemetri & Tren Agroklimat Interaktif (Interactive Charts)  --}}
    {{-- ========================================================================= --}}
    <article class="dashboard-panel">
        <div class="dashboard-panel__header">
            <div>
                <h2>Grafik Telemetri & Tren Agroklimat</h2>
                <p>Pantau fluktuasi temperatur udara, lengas tanah, dan probabilitas presipitasi secara dinamis.</p>
            </div>

            <div class="chart-tabs-wrap">
                <button type="button" class="chart-tab-btn is-active" data-period="24h" onclick="switchChartPeriod('24h')">
                    24 Jam Terakhir
                </button>
                <button type="button" class="chart-tab-btn" data-period="7d" onclick="switchChartPeriod('7d')">
                    Proyeksi 7 Hari
                </button>
                <button type="button" class="chart-tab-btn" data-period="monthly" onclick="switchChartPeriod('monthly')">
                    Tren Agrikultur
                </button>
            </div>
        </div>

        <div class="dashboard-chart-container">
            <canvas id="dashboardTelemetryChart"></canvas>
        </div>

        <div class="dashboard-chart-footer">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
            </svg>
            <span id="chartInsightText">
                Grafik 24 jam menunjukkan fluktuasi suhu dan kelembaban harian di zona perakaran sawah.
            </span>
        </div>
    </article>

    {{-- ========================================================================= --}}
    {{-- Mini GIS Live Radar & Pantauan Mikroklimat Lahan (2 Column Grid)          --}}
    {{-- ========================================================================= --}}
    <section class="dashboard-gis-climate-grid">

        {{-- Left: Mini GIS Live Map --}}
        <article class="dashboard-panel">
            <div class="dashboard-panel__header">
                <div>
                    <h2>Peta Mini GIS & Radar Lahan</h2>
                    <p>Sebaran spasial petak sawah dan status mikroklimat lahan.</p>
                </div>

                <div class="mini-gis-controls">
                    <button type="button" class="map-layer-btn is-active" data-layer="street" onclick="toggleMapLayer('street')">
                        Peta Jalan
                    </button>
                    <button type="button" class="map-layer-btn" data-layer="satellite" onclick="toggleMapLayer('satellite')">
                        Satelit
                    </button>
                    <a href="{{ route('admin.weather.map') }}" class="dashboard-panel__link" style="margin-left: 4px;">
                        Buka GIS Penuh →
                    </a>
                </div>
            </div>

            <div id="dashboard-mini-gis-map" class="dashboard-mini-gis-map"></div>
        </article>

        {{-- Right: Live Weather & Soil Gauges --}}
        <article class="dashboard-panel">
            <div class="dashboard-panel__header">
                <div>
                    <h2>Mikroklimat Lahan</h2>
                    <p>Parameter sensor IoT & cuaca petak sawah terpilih.</p>
                </div>

                @if(isset($farms) && $farms->isNotEmpty())
                    <div class="dashboard-select-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="dashboard-select-icon">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <select id="farm_select" name="farm_id" class="dashboard-farm-select">
                            <option value="">Semua Lahan (Rata-rata)</option>
                            @foreach($farms as $farm)
                                <option value="{{ $farm->id }}" {{ (string)$selectedFarmId === (string)$farm->id ? 'selected' : '' }}>
                                    {{ $farm->name }} ({{ $farm->farmer?->name ?? 'Petani' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            {{-- 4 Weather KPIs --}}
            <div class="dashboard-weather-kpis" style="padding: 16px 20px 0;">
                {{-- KPI 1: Suhu --}}
                <div class="dashboard-kpi-card" style="padding: 12px 14px; min-height: auto;">
                    <div class="dashboard-kpi-card__body">
                        <div>
                            <p class="dashboard-kpi-card__label">Suhu Udara</p>
                            <strong class="dashboard-kpi-card__value" id="kpi-weather-temp" data-countup="{{ $liveWeather['temp'] }}" data-countup-decimals="1" data-countup-suffix="°C" style="font-size: 24px;">
                                {{ number_format($liveWeather['temp'], 1, ',', '.') }}°C
                            </strong>
                        </div>
                        <span class="dashboard-kpi-card__icon dashboard-tone-green" style="width:34px; height:34px; flex:0 0 34px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>
                            </svg>
                        </span>
                    </div>
                </div>

                {{-- KPI 2: Kelembapan --}}
                <div class="dashboard-kpi-card" style="padding: 12px 14px; min-height: auto;">
                    <div class="dashboard-kpi-card__body">
                        <div>
                            <p class="dashboard-kpi-card__label">Kelembapan</p>
                            <strong class="dashboard-kpi-card__value" id="kpi-weather-humidity" data-countup="{{ $liveWeather['humidity'] }}" data-countup-suffix="%" style="font-size: 24px;">
                                {{ $liveWeather['humidity'] }}%
                            </strong>
                        </div>
                        <span class="dashboard-kpi-card__icon dashboard-tone-blue" style="width:34px; height:34px; flex:0 0 34px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                            </svg>
                        </span>
                    </div>
                </div>

                {{-- KPI 3: Peluang Hujan --}}
                <div class="dashboard-kpi-card" style="padding: 12px 14px; min-height: auto;">
                    <div class="dashboard-kpi-card__body">
                        <div>
                            <p class="dashboard-kpi-card__label">Peluang Hujan</p>
                            <strong class="dashboard-kpi-card__value" id="kpi-weather-rain" data-countup="{{ $liveWeather['rain_chance'] }}" data-countup-suffix="%" style="font-size: 24px;">
                                {{ $liveWeather['rain_chance'] }}%
                            </strong>
                        </div>
                        <span class="dashboard-kpi-card__icon dashboard-tone-orange" style="width:34px; height:34px; flex:0 0 34px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="M16 14v6"/><path d="M8 14v6"/><path d="M12 16v6"/>
                            </svg>
                        </span>
                    </div>
                </div>

                {{-- KPI 4: Lengas & Suhu Tanah --}}
                <div class="dashboard-kpi-card" style="padding: 12px 14px; min-height: auto;">
                    <div class="dashboard-kpi-card__body">
                        <div>
                            <p class="dashboard-kpi-card__label">Lengas Tanah</p>
                            <strong class="dashboard-kpi-card__value" id="kpi-weather-soil" data-countup="{{ $liveWeather['soil_moisture'] }}" data-countup-suffix="%" style="font-size: 24px;">
                                {{ $liveWeather['soil_moisture'] }}%
                            </strong>
                        </div>
                        <span class="dashboard-kpi-card__icon dashboard-tone-green" style="width:34px; height:34px; flex:0 0 34px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22v-9"/><path d="M9 15c1.5-3 5-3 5-7-4 0-5 3.5-5 7z"/><path d="M15 11c-1-2-3-2-3-5 3 0 4 2.5 3 5z"/>
                            </svg>
                        </span>
                    </div>
                    <p class="dashboard-kpi-card__helper" style="margin-top: 4px; font-size: 11.5px;">
                        Suhu Tanah: <strong id="kpi-soil-temp-val" style="color:var(--admin-text);">{{ number_format($liveWeather['soil_temp'] ?? 25.5, 1, ',', '.') }}°C</strong> • {{ $liveWeather['soil_moisture'] >= 60 ? 'Optimal' : 'Perlu Air' }}
                    </p>
                </div>
            </div>

            {{-- 2 Radial Gauges --}}
            <div class="dashboard-gauges-row">
                <div class="gauge-card">
                    <div class="gauge-svg-wrap">
                        <svg class="gauge-svg" width="60" height="60" viewBox="0 0 80 80">
                            <circle class="gauge-bg" cx="40" cy="40" r="36"/>
                            <circle id="gaugeSoilProgress" class="gauge-progress gauge-progress--soil" cx="40" cy="40" r="36"
                                stroke-dasharray="226.19"
                                stroke-dashoffset="{{ 226.19 - ($liveWeather['soil_moisture'] / 100 * 226.19) }}"/>
                        </svg>
                        <div class="gauge-center-text">{{ $liveWeather['soil_moisture'] }}%</div>
                    </div>
                    <div class="gauge-info">
                        <h5>Kadar Air Tanah</h5>
                        <p>{{ $liveWeather['soil_moisture'] >= 60 ? 'Kapasitas Lapang Baik' : 'Perlu Irigasi AWD' }}</p>
                    </div>
                </div>

                <div class="gauge-card">
                    <div class="gauge-svg-wrap">
                        <svg class="gauge-svg" width="60" height="60" viewBox="0 0 80 80">
                            <circle class="gauge-bg" cx="40" cy="40" r="36"/>
                            <circle id="gaugeRainProgress" class="gauge-progress gauge-progress--rain" cx="40" cy="40" r="36"
                                stroke-dasharray="226.19"
                                stroke-dashoffset="{{ 226.19 - ($liveWeather['rain_chance'] / 100 * 226.19) }}"/>
                        </svg>
                        <div class="gauge-center-text">{{ $liveWeather['rain_chance'] }}%</div>
                    </div>
                    <div class="gauge-info">
                        <h5>Risiko Curah Hujan</h5>
                        <p>{{ $liveWeather['rain_chance'] >= 70 ? 'Potensi Limpasan Air' : 'Presipitasi Aman' }}</p>
                    </div>
                </div>
            </div>

            {{-- 5-Day Forecast Strip --}}
            <div class="forecast-strip-panel">
                <h4>Proyeksi Cuaca 5 Hari BMKG</h4>
                <div class="forecast-strip">
                    @foreach($forecastDays as $fday)
                        <div class="forecast-strip-item">
                            <strong>{{ $fday['day'] }}</strong>
                            <small>{{ $fday['date'] }}</small>
                            <img src="https://openweathermap.org/img/wn/{{ $fday['icon'] }}.png" alt="{{ $fday['weather'] }}" class="forecast-strip-icon">
                            <span class="forecast-strip-temp">{{ $fday['temp_max'] }}° / {{ $fday['temp_min'] }}°</span>
                            <span class="forecast-strip-pop">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;">
                                    <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                                </svg>
                                {{ $fday['rain_pop'] }}%
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

        </article>

    </section>

    {{-- ========================================================================= --}}
    {{-- Radar Peringatan & Ancaman Bencana (Interactive SOP & Mitigasi Cards)      --}}
    {{-- ========================================================================= --}}
    <article class="dashboard-panel">
        <div class="dashboard-panel__header">
            <div>
                <h2>Radar Ancaman Bencana & Agroklimat</h2>
                <p>Klik kartu ancaman untuk membuka Prosedur Operasional Standar (SOP) & kirim instruksi mitigasi cepat.</p>
            </div>

            <div style="display:flex; align-items:center; gap:8px;">
                <span id="disasterSystemBadge" class="dashboard-threat-summary-badge dashboard-threat-summary-badge--{{ $disasterSummary['system_status'] }}">
                    {{ $disasterSummary['system_status'] === 'danger' ? 'Status Bahaya' : ($disasterSummary['system_status'] === 'warning' ? 'Status Siaga' : 'Status Aman') }}
                </span>
                <a href="{{ route('admin.broadcast.index') }}" class="dashboard-panel__link">
                    Kelola Peringatan Dini →
                </a>
            </div>
        </div>

        <div class="dashboard-disaster-grid" style="padding: 16px 20px;">
            @foreach($disasterThreats as $threat)
                <div class="dashboard-threat-clean-card is-clickable" onclick="openThreatDetailModal('{{ $threat['id'] }}')" title="Klik untuk lihat SOP Mitigasi">
                    <div class="dashboard-threat-clean-head">
                        <span class="dashboard-threat-clean-cat">{{ $threat['category_label'] }}</span>
                        <span class="dashboard-threat-clean-badge dashboard-threat-clean-badge--{{ $threat['severity'] }}">
                            {{ $threat['severity_label'] }}
                        </span>
                    </div>

                    <h3 class="dashboard-threat-clean-title">{{ $threat['title'] }}</h3>

                    <div class="dashboard-threat-clean-metrics">
                        @foreach($threat['metrics'] as $mk => $mv)
                            <span><strong>{{ $mk }}:</strong> {{ $mv }}</span>
                        @endforeach
                    </div>

                    <p class="dashboard-threat-clean-recom">
                        <strong>Rekomendasi:</strong> {{ $threat['recommendation'] }}
                    </p>

                    <div class="dashboard-threat-clean-footer">
                        <span class="dashboard-threat-clean-time">{{ $threat['timeframe'] }}</span>
                        <span class="dashboard-threat-clean-link">
                            Buka SOP & Aksi →
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </article>

    {{-- ========================================================================= --}}
    {{-- Aktivitas Audit & Notifikasi Sistem (Interactive Search & Filters)       --}}
    {{-- ========================================================================= --}}
    <section class="dashboard-overview">

        {{-- Aktivitas Audit --}}
        <article class="dashboard-panel dashboard-panel--activity">
            <div class="dashboard-panel__header">
                <div>
                    <h2>Aktivitas Terbaru</h2>
                    <p>Jejak log aksi operasional dan modul sistem P.A.D.I.</p>
                </div>

                <a href="{{ route('admin.audit.index') }}" class="dashboard-panel__link">
                    Audit log lengkap →
                </a>
            </div>

            {{-- Interactive Search and Tabs for Activities --}}
            <div class="activity-filter-header">
                <div class="activity-filter-top">
                    <div class="activity-search-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                        </svg>
                        <input type="search" id="activitySearchInput" placeholder="Cari aktivitas...">
                    </div>

                    <div class="activity-filter-tabs">
                        <button type="button" class="activity-filter-tab is-active" data-filter="all">Semua</button>
                        <button type="button" class="activity-filter-tab" data-filter="broadcast">Broadcast</button>
                        <button type="button" class="activity-filter-tab" data-filter="penyakit">Penyakit</button>
                        <button type="button" class="activity-filter-tab" data-filter="marketplace">Pasar</button>
                        <button type="button" class="activity-filter-tab" data-filter="pengguna">User</button>
                    </div>
                </div>
            </div>

            <div class="dashboard-activity-list" id="dashboardActivityList">
                @forelse($recentActivities as $activity)
                    <div class="dashboard-activity-item" data-module="{{ strtolower($activity['module']) }}">
                        <span class="dashboard-activity-icon dashboard-tone-{{ $activity['tone'] }}" aria-hidden="true">
                            @include('admin.partials.metric-icon', ['icon' => $activity['icon']])
                        </span>

                        <div class="dashboard-activity-main">
                            <h3>{{ $activity['title'] }}</h3>
                            <dl>
                                <div>
                                    <dt>Pelaku</dt>
                                    <dd>{{ $activity['actor'] }}</dd>
                                </div>
                                <div>
                                    <dt>Modul</dt>
                                    <dd>{{ $activity['module'] }}</dd>
                                </div>
                            </dl>
                        </div>

                        <time class="dashboard-activity-time">{{ $activity['time'] }}</time>
                    </div>
                @empty
                    <div class="dashboard-empty">
                        <span class="dashboard-empty__icon" aria-hidden="true">
                            @include('admin.partials.metric-icon', ['icon' => 'audit'])
                        </span>
                        <p>Belum ada aktivitas audit.</p>
                    </div>
                @endforelse

                <div id="activitySearchEmpty" class="dashboard-empty" style="display: none;">
                    <p>Tidak ada aktivitas yang sesuai pencarian atau filter.</p>
                </div>
            </div>
        </article>

        {{-- Notifikasi Sistem --}}
        <article class="dashboard-panel">
            <div class="dashboard-panel__header">
                <div>
                    <h2>Notifikasi Sistem</h2>
                    <p>Pemberitahuan terkini untuk administrator.</p>
                </div>

                <span class="dashboard-panel__badge" id="adminNotifBadge">
                    {{ number_format($adminUnreadNotifications ?? 0, 0, ',', '.') }} belum dibaca
                </span>
            </div>

            <div class="dashboard-notification-list">
                @forelse($systemNotifications as $notification)
                    <div class="dashboard-notification-item">
                        <span class="dashboard-notification-type" aria-hidden="true">
                            {{ strtoupper(substr($notification['type'], 0, 1)) }}
                        </span>

                        <div>
                            <h3>{{ $notification['title'] }}</h3>
                            <p>{{ $notification['body'] }}</p>
                            <time>{{ $notification['time'] }}</time>
                        </div>
                    </div>
                @empty
                    <div class="dashboard-empty">
                        <span class="dashboard-empty__icon" aria-hidden="true">
                            @include('admin.partials.metric-icon', ['icon' => 'broadcast'])
                        </span>
                        <p>Belum ada notifikasi baru.</p>
                    </div>
                @endforelse
            </div>

            <div class="dashboard-panel__footer-action">
                <a href="{{ route('admin.notifications.index') }}" class="dashboard-view-all">
                    Lihat semua notifikasi →
                </a>
            </div>

            @if(($adminUnreadNotifications ?? 0) > 0)
                <form method="POST" action="{{ route('admin.notifications.read') }}" class="dashboard-panel__footer-action">
                    @csrf
                    <button type="submit">Tandai semua dibaca</button>
                </form>
            @endif
        </article>

    </section>

    {{-- ========================================================================= --}}
    {{-- Status Pengguna & Marketplace Stats                                       --}}
    {{-- ========================================================================= --}}
    <section class="dashboard-monitoring" aria-label="Monitoring operasional">

        <article class="dashboard-panel">
            <div class="dashboard-panel__header">
                <div>
                    <h2>Status Pengguna & Petani</h2>
                    <p>Kondisi akun dan broadcast yang perlu dipantau.</p>
                </div>

                <a href="{{ route('admin.users.index') }}" class="dashboard-panel__link">
                    Kelola pengguna →
                </a>
            </div>

            <dl class="dashboard-definition-grid">
                <div>
                    <dt>Aktif</dt>
                    <dd data-countup="{{ $userStats['active'] }}">{{ number_format($userStats['active'], 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt>Tidak aktif</dt>
                    <dd data-countup="{{ $userStats['inactive'] }}">{{ number_format($userStats['inactive'], 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt>Ditangguhkan</dt>
                    <dd data-countup="{{ $userStats['suspended'] }}">{{ number_format($userStats['suspended'], 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt>Broadcast</dt>
                    <dd data-countup="{{ $userStats['broadcasts'] }}">{{ number_format($userStats['broadcasts'], 0, ',', '.') }}</dd>
                </div>
            </dl>
        </article>

        <article class="dashboard-panel">
            <div class="dashboard-panel__header">
                <div>
                    <h2>Pasar & Marketplace Panen</h2>
                    <p>Listing, penawaran, dan kontrak pembelian hasil tani.</p>
                </div>

                <a href="{{ route('admin.marketplace.index') }}" class="dashboard-panel__link">
                    Lihat marketplace →
                </a>
            </div>

            <dl class="dashboard-definition-grid">
                <div>
                    <dt>Listing aktif</dt>
                    <dd data-countup="{{ $marketplaceStats['active_listings'] }}">{{ number_format($marketplaceStats['active_listings'], 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt>Penawaran</dt>
                    <dd data-countup="{{ $marketplaceStats['offers'] }}">{{ number_format($marketplaceStats['offers'], 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt>Kontrak aktif</dt>
                    <dd data-countup="{{ $marketplaceStats['contracts'] }}">{{ number_format($marketplaceStats['contracts'], 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt>Draft/moderasi</dt>
                    <dd data-countup="{{ $marketplaceStats['pending_moderation'] }}">{{ number_format($marketplaceStats['pending_moderation'], 0, ',', '.') }}</dd>
                </div>
            </dl>
        </article>

    </section>

</div>

{{-- ========================================================================= --}}
{{-- MODAL 1: Quick Broadcast Dialog                                           --}}
{{-- ========================================================================= --}}
<div id="modalQuickBroadcast" class="dashboard-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="qbTitle">
    <div class="dashboard-modal-dialog">
        <div class="modal-header">
            <div style="display:flex; align-items:center; gap:8px;">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--admin-primary);">
                    <path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>
                </svg>
                <h3 id="qbTitle" style="margin:0; font-size:16px; font-weight:800;">Kirim Broadcast Peringatan & Imbauan</h3>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeQuickBroadcastModal()" aria-label="Tutup modal">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6 6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.broadcast.store') }}">
            @csrf
            <div class="modal-body">
                <div class="qb-form-group">
                    <label for="qb_preset">Gunakan Template Rekomendasi Cepat:</label>
                    <select id="qb_preset" class="qb-select" onchange="applyBroadcastPreset(this.value)">
                        <option value="">-- Pilih Template Pesan Siaga --</option>
                        <option value="flood">Peringatan Dini Banjir & Curah Hujan Lebat</option>
                        <option value="pest">Waspada Ledakan Hama Wereng & Blas</option>
                        <option value="storm">Peringatan Angin Kencang / Rebah Rumpun</option>
                        <option value="fertilizer">Rekomendasi Jadwal Pemupukan Susulan</option>
                    </select>
                </div>

                <div class="qb-form-group">
                    <label for="qb_title">Judul Broadcast <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="qb_title" name="title" required placeholder="Contoh: Peringatan Cuaca Ekstrem..." class="qb-input">
                </div>

                <div class="qb-form-group">
                    <label for="qb_type">Tingkat Urgensi / Tipe <span style="color:#ef4444;">*</span></label>
                    <select id="qb_type" name="type" required class="qb-select">
                        <option value="info">Informasi (Normal)</option>
                        <option value="warning" selected>Peringatan (Siaga / Warning)</option>
                        <option value="emergency">Darurat (Kritis / Emergency)</option>
                    </select>
                </div>

                <div class="qb-form-group">
                    <label for="qb_message">Isi Pesan Peringatan & Instruksi <span style="color:#ef4444;">*</span></label>
                    <textarea id="qb_message" name="message" rows="4" required placeholder="Tuliskan rekomendasi dan tindakan mitigasi untuk petani..." class="qb-textarea"></textarea>
                </div>
            </div>

            <input type="hidden" name="status" value="published">
            <input type="hidden" name="target_role" value="all">

            <div class="modal-footer">
                <button type="button" class="btn-dash-action" onclick="closeQuickBroadcastModal()">Batal</button>
                <button type="submit" class="btn-dash-action btn-dash-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>
                    </svg>
                    Kirim Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 2: Threat Detail & SOP Mitigasi Dialog                              --}}
{{-- ========================================================================= --}}
<div id="modalThreatDetail" class="dashboard-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="td_title">
    <div class="dashboard-modal-dialog dashboard-modal-dialog--wide">
        <div class="modal-header">
            <div class="modal-threat-head" style="gap: 10px;">
                <h3 id="td_title">Detail Mitigasi Ancaman</h3>
                <span id="td_severity" class="modal-threat-badge modal-threat-badge--danger">Bahaya</span>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeThreatDetailModal()" aria-label="Tutup modal">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6 6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="modal-body">
            <div>
                <span id="td_category" style="font-size: 11px; font-weight: 750; color: var(--admin-text-muted); text-transform: uppercase;">Kategori</span>
                <p id="td_subtitle" style="margin: 4px 0 0; color: var(--admin-text-secondary); font-size: 13.5px; line-height: 1.5;"></p>
            </div>

            <div>
                <label style="font-size: 12px; font-weight: 750; color: var(--admin-text); display: block; margin-bottom: 6px;">Parameter Sensor & Kondisi Lapangan:</label>
                <div id="td_metrics_grid" class="td-metrics-grid"></div>
            </div>

            <div class="td-sop-box">
                <div style="display:flex; align-items:center; gap:6px; margin-bottom:4px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--admin-primary);">
                        <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                    <strong>Prosedur Standar Operasional (SOP):</strong>
                </div>
                <p id="td_recom" style="margin: 0;"></p>
            </div>

            <div style="display:flex; justify-content:space-between; font-size:12px; color:var(--admin-text-secondary); border-top:1px solid var(--admin-border); padding-top:10px;">
                <span>Waktu Respons: <strong id="td_timeframe" style="color:var(--admin-text);">-</strong></span>
                <span>Cakupan Dampak: <strong id="td_impact" style="color:var(--admin-text);">-</strong></span>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-dash-action" onclick="closeThreatDetailModal()">Tutup</button>
            <button type="button" id="td_btn_broadcast" class="btn-dash-action btn-dash-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>
                </svg>
                <span>Kirim Broadcast Mitigasi ke Petani</span>
            </button>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 3: Quick Briefing Report Dialog                                     --}}
{{-- ========================================================================= --}}
<div id="modalQuickReport" class="dashboard-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="qrTitle">
    <div class="dashboard-modal-dialog">
        <div class="modal-header">
            <div style="display:flex; align-items:center; gap:8px;">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--admin-primary);">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                </svg>
                <h3 id="qrTitle" style="margin:0; font-size:16px; font-weight:800;">Ringkasan Operasional Harian P.A.D.I.</h3>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeQuickReportModal()" aria-label="Tutup modal">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6 6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="modal-body" id="printableReportSection">
            <div style="text-align: center; border-bottom: 2px solid var(--admin-border); padding-bottom: 12px;">
                <h4 style="margin:0; font-size: 16px; font-weight: 800;">LAPORAN MONITORING AGROKLIMAT & LAHAN</h4>
                <p style="margin: 4px 0 0; font-size: 12px; color: var(--admin-text-secondary);">
                    Tanggal: <strong>{{ now()->translatedFormat('l, d F Y') }}</strong> • Status: <strong>{{ $disasterSummary['system_status'] === 'danger' ? 'SIAGA BAHAYA' : 'TERKENDALI' }}</strong>
                </p>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 12.5px;">
                <div style="background:var(--admin-surface-secondary); padding: 10px; border-radius: 6px;">
                    <span style="color:var(--admin-text-muted); font-size:11px;">Rata-rata Suhu Lahan:</span>
                    <strong style="display:block; font-size:16px;">{{ $liveWeather['temp'] }}°C</strong>
                </div>
                <div style="background:var(--admin-surface-secondary); padding: 10px; border-radius: 6px;">
                    <span style="color:var(--admin-text-muted); font-size:11px;">Kelembapan & Lengas:</span>
                    <strong style="display:block; font-size:16px;">{{ $liveWeather['humidity'] }}% / {{ $liveWeather['soil_moisture'] }}%</strong>
                </div>
            </div>

            <div style="font-size: 12.5px;">
                <strong>Ringkasan Radar Bencana:</strong>
                <ul style="margin: 6px 0 0; padding-left: 20px; line-height: 1.6;">
                    @foreach($disasterThreats as $t)
                        <li><strong>{{ $t['title'] }}:</strong> {{ $t['recommendation'] }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-dash-action" onclick="closeQuickReportModal()">Tutup</button>
            <button
                type="button"
                class="btn-dash-action btn-dash-primary"
                onclick="window.location.href='{{ route('admin.report.download') }}'"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v-5a2 2 0 0 1-2 2h-2M6 14h12v8H6z"/>
                </svg>
                Cetak Dokumen
            </button>
        </div>
    </div>
</div>

{{-- Interactive Client Scripts --}}
<script src="{{ asset('js/admin/dashboard-interactive.js') }}"></script>

@endsection

