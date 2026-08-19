@extends('layouts.admin')

@section('content')

<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">

@php
    $metricLinks = [
        'users' => route('admin.users.index'),
        'farm' => route('admin.agriculture.index'),
        'warning' => route('admin.disease.index'),
        'market' => route('admin.marketplace.index'),
    ];
@endphp

<div class="dashboard-page">

    @if(session('status'))
        <div class="dashboard-alert" role="status">
            {{ session('status') }}
        </div>
    @endif

    <header class="dashboard-header">
        <div>
            <nav class="dashboard-breadcrumb" aria-label="Breadcrumb">
                <span>Admin</span>
                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span>Dashboard</span>
            </nav>

            <h1>Dashboard</h1>
            <p>Pantau kondisi operasional dan aktivitas terbaru P.A.D.I.</p>
        </div>

        <div class="dashboard-period" aria-label="Periode data">
            <span>Data hari ini</span>
            <strong>{{ now()->translatedFormat('d F Y') }}</strong>
        </div>
    </header>

    <section class="dashboard-kpi-grid" aria-label="Ringkasan metrik operasional">
        @foreach($metrics as $metric)
            @include('admin.partials.kpi-card', [
                'metric' => $metric,
                'href' => $metricLinks[$metric['icon']] ?? null,
            ])
        @endforeach
    </section>

    {{-- ========================================================================= --}}
    {{-- Pantauan Cuaca & Mikroklimat Lahan (Consistent Dashboard Panel)           --}}
    {{-- ========================================================================= --}}
    <article class="dashboard-panel">
        <div class="dashboard-panel__header">
            <div>
                <h2>Pantauan Cuaca & Mikroklimat</h2>
                <p>Kondisi iklim dan cuaca lahan pertanian real-time.</p>
            </div>

            <div class="dashboard-weather-controls">
                @if(isset($farms) && $farms->isNotEmpty())
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="dashboard-farm-form">
                        <label for="farm_select" class="sr-only">Pilih Lokasi Lahan</label>
                        <div class="dashboard-select-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="dashboard-select-icon">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <select id="farm_select" name="farm_id" onchange="this.form.submit()" class="dashboard-farm-select">
                                <option value="">Semua Lahan (Rata-rata)</option>
                                @foreach($farms as $farm)
                                    <option value="{{ $farm->id }}" {{ (string)$selectedFarmId === (string)$farm->id ? 'selected' : '' }}>
                                        Lahan {{ $farm->name }} ({{ $farm->farmer?->name ?? 'Petani' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                @endif

                <a href="{{ route('admin.weather.index') }}" class="dashboard-panel__link">
                    Lihat peta cuaca
                </a>
            </div>
        </div>

        <div class="dashboard-weather-kpis">
            {{-- KPI 1: Suhu & Kondisi --}}
            <div class="dashboard-kpi-card">
                <div class="dashboard-kpi-card__body">
                    <div>
                        <p class="dashboard-kpi-card__label">Suhu Udara</p>
                        <strong class="dashboard-kpi-card__value">{{ number_format($liveWeather['temp'], 1, ',', '.') }}<span style="font-size: 20px; font-weight: 600; color: var(--admin-text-muted);">°C</span></strong>
                    </div>
                    <span class="dashboard-kpi-card__icon dashboard-tone-green" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>
                        </svg>
                    </span>
                </div>
                <p class="dashboard-kpi-card__helper">{{ $liveWeather['condition_title'] ?? 'Berawan' }} • Terasa {{ $liveWeather['feels_like'] }}°C</p>
            </div>

            {{-- KPI 2: Kelembapan --}}
            <div class="dashboard-kpi-card">
                <div class="dashboard-kpi-card__body">
                    <div>
                        <p class="dashboard-kpi-card__label">Kelembapan Udara</p>
                        <strong class="dashboard-kpi-card__value">{{ $liveWeather['humidity'] }}<span style="font-size: 20px; font-weight: 600; color: var(--admin-text-muted);">%</span></strong>
                    </div>
                    <span class="dashboard-kpi-card__icon dashboard-tone-blue" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                        </svg>
                    </span>
                </div>
                <p class="dashboard-kpi-card__helper">{{ $liveWeather['humidity'] >= 80 ? 'Kelembapan tinggi' : 'Kelembapan optimal' }}</p>
            </div>

            {{-- KPI 3: Peluang Hujan --}}
            <div class="dashboard-kpi-card">
                <div class="dashboard-kpi-card__body">
                    <div>
                        <p class="dashboard-kpi-card__label">Peluang Hujan</p>
                        <strong class="dashboard-kpi-card__value">{{ $liveWeather['rain_chance'] }}<span style="font-size: 20px; font-weight: 600; color: var(--admin-text-muted);">%</span></strong>
                    </div>
                    <span class="dashboard-kpi-card__icon dashboard-tone-orange" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="M16 14v6"/><path d="M8 14v6"/><path d="M12 16v6"/>
                        </svg>
                    </span>
                </div>
                <p class="dashboard-kpi-card__helper">{{ $liveWeather['rain_chance'] >= 70 ? 'Potensi hujan lebat lokal' : 'Kondisi relatif stabil' }}</p>
            </div>

            {{-- KPI 4: Lengas Tanah & Angin --}}
            <div class="dashboard-kpi-card">
                <div class="dashboard-kpi-card__body">
                    <div>
                        <p class="dashboard-kpi-card__label">Lengas Tanah</p>
                        <strong class="dashboard-kpi-card__value">{{ $liveWeather['soil_moisture'] }}<span style="font-size: 20px; font-weight: 600; color: var(--admin-text-muted);">%</span></strong>
                    </div>
                    <span class="dashboard-kpi-card__icon dashboard-tone-green" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22v-9"/><path d="M9 15c1.5-3 5-3 5-7-4 0-5 3.5-5 7z"/><path d="M15 11c-1-2-3-2-3-5 3 0 4 2.5 3 5z"/>
                        </svg>
                    </span>
                </div>
                <p class="dashboard-kpi-card__helper">Angin {{ $liveWeather['wind_speed'] }} km/j • {{ $liveWeather['location_name'] }}</p>
            </div>
        </div>
    </article>

    {{-- ========================================================================= --}}
    {{-- Radar Peringatan & Ancaman Bencana (Consistent Dashboard Panel)           --}}
    {{-- ========================================================================= --}}
    <article class="dashboard-panel">
        <div class="dashboard-panel__header">
            <div>
                <h2>Radar Ancaman Bencana Pertanian</h2>
                <p>Peringatan dini otomatis berbasis sensor agroklimat dan laporan lapangan.</p>
            </div>

            <a href="{{ route('admin.early-warning.index') }}" class="dashboard-panel__link">
                Kirim broadcast peringatan
            </a>
        </div>

        <div class="dashboard-disaster-grid">
            @foreach($disasterThreats as $threat)
                <div class="dashboard-threat-clean-card">
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
                        <a href="{{ $threat['action_route'] }}" class="dashboard-threat-clean-link">
                            {{ $threat['action_label'] }} →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </article>

    <section class="dashboard-overview">

        <article class="dashboard-panel dashboard-panel--activity">
            <div class="dashboard-panel__header">
                <div>
                    <h2>Aktivitas Terbaru</h2>
                    <p>Jejak aksi operasional dari audit log admin.</p>
                </div>

                <a href="{{ route('admin.audit.index') }}" class="dashboard-panel__link">
                    Lihat audit log
                </a>
            </div>

            <div class="dashboard-activity-list">
                @forelse($recentActivities as $activity)
                    <div class="dashboard-activity-item">
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
            </div>
        </article>

        <article class="dashboard-panel">
            <div class="dashboard-panel__header">
                <div>
                    <h2>Notifikasi Sistem</h2>
                    <p>Pesan terbaru untuk administrator.</p>
                </div>

                <span class="dashboard-panel__badge">
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

            @if(($adminUnreadNotifications ?? 0) > 0)
                <form method="POST" action="{{ route('admin.notifications.read') }}" class="dashboard-panel__footer-action">
                    @csrf
                    <button type="submit">Tandai semua dibaca</button>
                </form>
            @endif
        </article>

    </section>

    <section class="dashboard-monitoring" aria-label="Monitoring operasional">

        <article class="dashboard-panel">
            <div class="dashboard-panel__header">
                <div>
                    <h2>Status Pengguna</h2>
                    <p>Kondisi akun dan broadcast yang perlu dipantau.</p>
                </div>

                <a href="{{ route('admin.users.index') }}" class="dashboard-panel__link">
                    Kelola pengguna
                </a>
            </div>

            <dl class="dashboard-definition-grid">
                <div>
                    <dt>Aktif</dt>
                    <dd>{{ number_format($userStats['active'], 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt>Tidak aktif</dt>
                    <dd>{{ number_format($userStats['inactive'], 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt>Ditangguhkan</dt>
                    <dd>{{ number_format($userStats['suspended'], 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt>Broadcast</dt>
                    <dd>{{ number_format($userStats['broadcasts'], 0, ',', '.') }}</dd>
                </div>
            </dl>
        </article>

        <article class="dashboard-panel">
            <div class="dashboard-panel__header">
                <div>
                    <h2>Marketplace</h2>
                    <p>Listing, penawaran, dan kontrak pembelian.</p>
                </div>

                <a href="{{ route('admin.marketplace.index') }}" class="dashboard-panel__link">
                    Lihat marketplace
                </a>
            </div>

            <dl class="dashboard-definition-grid">
                <div>
                    <dt>Listing aktif</dt>
                    <dd>{{ number_format($marketplaceStats['active_listings'], 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt>Penawaran</dt>
                    <dd>{{ number_format($marketplaceStats['offers'], 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt>Kontrak aktif</dt>
                    <dd>{{ number_format($marketplaceStats['contracts'], 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt>Draft/moderasi</dt>
                    <dd>{{ number_format($marketplaceStats['pending_moderation'], 0, ',', '.') }}</dd>
                </div>
            </dl>
        </article>

    </section>

</div>

@endsection
