@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<link rel="stylesheet" href="{{ asset('css/admin/agriculture.css') }}">

<div class="pertanian-page">
    {{-- Breadcrumb Navigasi --}}
    <nav class="pertanian-breadcrumb" aria-label="Breadcrumb">
        <span>Admin</span>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="pertanian-breadcrumb-current">Pertanian &amp; Lahan</span>
    </nav>

    {{-- Header Halaman --}}
    <div class="pertanian-header">
        <div class="pertanian-header-content">
            <h1 class="pertanian-title">Manajemen Pertanian &amp; Lahan</h1>
            <p class="pertanian-description">Kelola data lahan petani, sistem irigasi, jadwal siklus tanam, dan estimasi waktu panen secara terpadu.</p>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="{{ route('admin.knowledge.index') }}" class="btn-add-land" style="background:#e8f5e9; color:#1b5e20; border:1px solid #81c784; box-shadow:none;">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>Pusat Pengetahuan</span>
            </a>

            <button type="button" class="btn-add-land" onclick="openCreateFarmModal()">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Lahan Baru</span>
            </button>
        </div>
    </div>

    {{-- Notifikasi Status / Pesan Sistem --}}
    @if(session('status'))
        <div class="pertanian-alert pertanian-alert-success" id="alert-status">
            <span>{{ session('status') }}</span>
            <button type="button" class="pertanian-alert-close" onclick="document.getElementById('alert-status').remove()">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="pertanian-alert pertanian-alert-danger" id="alert-errors">
            <span>{{ $errors->first() }}</span>
            <button type="button" class="pertanian-alert-close" onclick="document.getElementById('alert-errors').remove()">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- Kartu Ringkasan Indikator (KPI) --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Total Lahan</p>
                <h3 class="stat-number">{{ number_format($stats['farms'], 0, ',', '.') }}</h3>
                <p class="stat-description">Lahan sawah terdaftar</p>
            </div>
            <div class="stat-icon stat-icon-green">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2h1.5a2.5 2.5 0 002.5-2.5V7a2 2 0 00-2-2h-2c-.6 0-1.1-.3-1.4-.8l-1.2-2M15 21a6 6 0 100-12 6 6 0 000 12z"/>
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Total Luas Wilayah</p>
                <h3 class="stat-number">{{ number_format($stats['area'], 2, ',', '.') }} <span style="font-size:16px; font-weight:600;">ha</span></h3>
                <p class="stat-description">Luasan sawah produktif</p>
            </div>
            <div class="stat-icon stat-icon-emerald">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Musim Tanam Aktif</p>
                <h3 class="stat-number">{{ number_format($stats['active_seasons'], 0, ',', '.') }}</h3>
                <p class="stat-description">Siklus tanam berjalan</p>
            </div>
            <div class="stat-icon stat-icon-orange">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Riwayat Panen</p>
                <h3 class="stat-number">{{ number_format($stats['harvests'], 0, ',', '.') }}</h3>
                <p class="stat-description">Laporan panen tercatat</p>
            </div>
            <div class="stat-icon stat-icon-blue">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Tab Segmented Control Navigasi --}}
    <div class="agriculture-tabs-nav">
        <button type="button" class="agriculture-tab-btn is-active" onclick="switchAgricultureTab('tab-lahan', this)">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
            <span>Daftar Lahan Pertanian</span>
            <span class="agriculture-tab-badge">{{ $farms->total() }}</span>
        </button>

        <button type="button" class="agriculture-tab-btn" onclick="switchAgricultureTab('tab-jadwal', this)">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span>Jadwal &amp; Usia Tanam (HST)</span>
            <span class="agriculture-tab-badge">{{ count($concreteSchedules ?? []) }}</span>
        </button>

        <button type="button" class="agriculture-tab-btn" onclick="switchAgricultureTab('tab-irigasi', this)">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
            <span>Monitoring Pengairan &amp; Irigasi</span>
            <span class="agriculture-tab-badge">{{ count($irrigationAlerts ?? []) }}</span>
        </button>

        <button type="button" class="agriculture-tab-btn" onclick="switchAgricultureTab('tab-kalkulator', this)">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            <span>Kalkulator Waktu Tanam</span>
        </button>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 1: DAFTAR LAHAN PERTANIAN --}}
    {{-- ========================================================================= --}}
    <div id="tab-lahan" class="agriculture-tab-content is-active">
        <section class="data-card">
            <div class="data-header">
                <div>
                    <h2>Daftar Lahan Pertanian Terdaftar</h2>
                    <p>Menampilkan {{ $farms->firstItem() ?? 0 }} - {{ $farms->lastItem() ?? 0 }} dari {{ $farms->total() }} total lahan</p>
                </div>
            </div>

            {{-- Form Pencarian & Filter --}}
            <div class="filter-wrapper">
                <form method="GET" action="{{ route('admin.agriculture.index') }}" class="filter-form">
                    <div class="search-box">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari nama lahan, pemilik, atau catatan...">
                    </div>

                    <select name="irrigation" class="filter-select" onchange="this.form.submit()">
                        <option value="">Semua Tipe Irigasi</option>
                        <option value="teknis" @selected(($filters['irrigation'] ?? '') === 'teknis')>Irigasi Teknis</option>
                        <option value="setengah_teknis" @selected(($filters['irrigation'] ?? '') === 'setengah_teknis')>Setengah Teknis</option>
                        <option value="hujan" @selected(($filters['irrigation'] ?? '') === 'hujan')>Tadah Hujan</option>
                        <option value="swamp" @selected(($filters['irrigation'] ?? '') === 'swamp')>Rawa / Pasang Surut</option>
                        <option value="lainnya" @selected(($filters['irrigation'] ?? '') === 'lainnya')>Lainnya</option>
                    </select>

                    <button type="submit" class="btn-filter-submit">Terapkan</button>
                    @if(($filters['search'] ?? '') || ($filters['irrigation'] ?? ''))
                        <a href="{{ route('admin.agriculture.index') }}" class="btn-filter-reset">Reset</a>
                    @endif
                </form>
            </div>

            {{-- Tabel Data Lahan --}}
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Lahan &amp; Catatan</th>
                            <th>Pemilik (Petani)</th>
                            <th>Wilayah</th>
                            <th>Luas Wilayah</th>
                            <th>Sistem Pengairan</th>
                            <th>Koordinat GPS</th>
                            <th>Batas Polygon</th>
                            <th>Musim Tanam</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($farms as $farm)
                            @php
                                $irrigationThemes = [
                                    'teknis' => 'irrigation-teknis',
                                    'setengah_teknis' => 'irrigation-teknis',
                                    'hujan' => 'irrigation-hujan',
                                    'tadah_hujan' => 'irrigation-hujan',
                                    'swamp' => 'irrigation-rawa',
                                    'lainnya' => 'irrigation-lainnya',
                                ];
                                $irrigationLabels = [
                                    'teknis' => 'Irigasi Teknis',
                                    'setengah_teknis' => 'Setengah Teknis',
                                    'hujan' => 'Tadah Hujan',
                                    'tadah_hujan' => 'Tadah Hujan',
                                    'swamp' => 'Rawa Pasang Surut',
                                    'lainnya' => 'Lainnya',
                                ];
                                $pointsCount = is_array($farm->boundary_coordinates) ? count($farm->boundary_coordinates) : 0;
                            @endphp
                            <tr>
                                <td class="farm-name-cell">
                                    <p>{{ $farm->name }}</p>
                                    <span>{{ $farm->irrigation_notes ?: 'Kondisi lahan normal' }}</span>
                                </td>
                                <td>
                                    <div class="farmer-cell">
                                        <div class="farmer-avatar">
                                            {{ strtoupper(substr($farm->farmer?->name ?? 'P', 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="farmer-name">{{ $farm->farmer?->name ?? 'Petani Terdaftar' }}</p>
                                            <p class="farmer-phone">{{ $farm->farmer?->phone ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 13px; line-height: 1.5;">
                                        <div style="font-weight: 600; color: #0f172a;">
                                            {{ $farm->province?->name ?? '-' }}
                                        </div>
                                        <div style="color: #475569;">
                                            {{ $farm->regency?->name ?? '-' }}
                                        </div>
                                        <div style="color: #64748b;">
                                            Kec. {{ $farm->district?->name ?? '-' }}
                                        </div>
                                        <div style="color: #64748b;">
                                            Kel. {{ $farm->village?->name ?? '-' }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="area-badge">
                                        {{ number_format((float) $farm->area_ha, 2, ',', '.') }} ha
                                    </span>
                                </td>
                                <td>
                                    <span class="irrigation-badge {{ $irrigationThemes[$farm->irrigation_type] ?? 'irrigation-lainnya' }}">
                                        {{ $irrigationLabels[$farm->irrigation_type] ?? ucfirst($farm->irrigation_type) }}
                                    </span>
                                </td>
                                <td>
                                    @if($farm->latitude && $farm->longitude)
                                        <a href="https://maps.google.com/?q={{ $farm->latitude }},{{ $farm->longitude }}" target="_blank" class="coord-badge" title="Buka lokasi di Google Maps">
                                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            {{ number_format((float)$farm->latitude, 4) }}, {{ number_format((float)$farm->longitude, 4) }}
                                        </a>
                                    @else
                                        <span style="color:#94a3b8; font-size:13px;">Titik Otomatis</span>
                                    @endif
                                </td>
                                <td>
                                    @if($pointsCount >= 3)
                                        <span class="irrigation-badge irrigation-teknis" style="font-size:11px;">
                                            {{ $pointsCount }} Titik Batas
                                        </span>
                                    @elseif($pointsCount > 0)
                                        <span class="irrigation-badge irrigation-hujan" style="font-size:11px;">
                                            {{ $pointsCount }} Titik
                                        </span>
                                    @else
                                        <span style="color:#94a3b8; font-size:12px;">1 Titik Tengah</span>
                                    @endif
                                </td>
                                <td>
                                    <span style="font-weight:700; color:#0f172a;">{{ $farm->cropSeasons->count() }}</span>
                                    <span style="color:#64748b; font-size:12px;"> Musim</span>
                                </td>
                                <td class="action-cell">
                                    <button
                                        type="button"
                                        class="btn-edit-land"
                                        onclick="openEditFarmModal({{ json_encode([
                                            'id' => $farm->id,
                                            'farmer_user_id' => $farm->farmer_user_id,
                                            'name' => $farm->name,
                                            'area_ha' => $farm->area_ha,
                                            'latitude' => $farm->latitude,
                                            'longitude' => $farm->longitude,
                                            'boundary_coordinates' => $farm->boundary_coordinates,
                                            'irrigation_type' => $farm->irrigation_type,
                                            'irrigation_notes' => $farm->irrigation_notes,
                                            'update_url' => route('admin.agriculture.update', $farm),
                                        ]) }})"
                                    >
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </button>

                                    @if(auth()->user()?->hasRole(\App\Enums\UserRole::Admin->value))
                                    <form method="POST" action="{{ route('admin.agriculture.destroy', $farm) }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="btn-delete-land"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus data lahan {{ $farm->name }}?')"
                                        >
                                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Hapus
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="pertanian-empty">
                                    Belum ada data lahan pertanian yang sesuai dengan filter pencarian.
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
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 2: JADWAL & USIA TANAM (HST) --}}
    {{-- ========================================================================= --}}
    <div id="tab-jadwal" class="agriculture-tab-content">
        <section class="data-card">
            <div class="data-header">
                <div>
                    <h2>Jadwal Siklus Tanam &amp; Usia Tanaman (HST)</h2>
                    <p>Pantau perkembangan usia padi (Hari Setelah Tanam), fase biologis, dan rekomendasi perawatan mingguan</p>
                </div>
                <span style="font-size:12px; font-weight:700; color:#15803d; background:#e8f5e9; padding:4px 12px; border-radius:20px;">
                    {{ count($concreteSchedules ?? []) }} Siklus Berjalan
                </span>
            </div>

            <div style="padding: 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 18px;">
                @forelse($concreteSchedules ?? [] as $sch)
                    <div style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; background: #ffffff; box-shadow: 0 1px 4px rgba(0,0,0,0.04); display:flex; flex-direction:column; justify-content:space-between;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                                <div>
                                    <strong style="font-size: 15px; color: #0f172a; display: block;">{{ $sch['farm_name'] }}</strong>
                                    <span style="font-size: 12px; color: #64748b;">Pemilik: {{ $sch['farmer_name'] }} &bull; Varietas: <strong style="color: #166534;">{{ $sch['variety_name'] }}</strong> ({{ $sch['maturity_days'] }} Hari)</span>
                                </div>
                                <span class="irrigation-badge {{ $sch['badge_class'] }}" style="font-size: 11px;">
                                    {{ $sch['status_label'] }}
                                </span>
                            </div>

                            {{-- Progress Bar HST --}}
                            <div style="margin: 14px 0 10px;">
                                <div style="display: flex; justify-content: space-between; font-size: 11.5px; font-weight: 600; color: #475569; margin-bottom: 5px;">
                                    <span>Tanam: <strong>{{ $sch['planting_date'] }}</strong></span>
                                    <span>Target Panen: <strong style="color: #166534;">{{ $sch['harvest_date'] }}</strong></span>
                                </div>
                                <div style="height: 9px; background: #f1f5f9; border-radius: 6px; overflow: hidden; border:1px solid #e2e8f0;">
                                    <div style="width: {{ $sch['progress_pct'] }}%; height: 100%; background: linear-gradient(90deg, #22c55e, #15803d); border-radius: 6px;"></div>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-size: 11.5px; color: #64748b; margin-top: 5px;">
                                    <span>Fase: <strong>{{ $sch['phase'] }}</strong></span>
                                    <span style="font-weight: 750; color: {{ $sch['days_remaining'] <= 14 ? '#dc2626' : '#15803d' }};">
                                        Sisa {{ $sch['days_remaining'] }} Hari Panen
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Panduan Lapangan --}}
                        <div style="background: #f8fafc; border-left: 3px solid #166534; padding: 10px 12px; border-radius: 0 8px 8px 0; margin-top: 12px;">
                            <span style="font-size: 10px; font-weight: 750; color: #166534; text-transform: uppercase; letter-spacing:0.04em;">Instruksi Perawatan Lapangan:</span>
                            <p style="font-size: 11.5px; color: #334155; margin: 3px 0 0 0; line-height: 1.45;">
                                {{ $sch['action'] }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; color: #64748b; font-size: 13.5px; padding: 32px 20px;">
                        Belum ada siklus musim tanam yang tercatat.
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 3: MONITORING IRIGASI & AIR --}}
    {{-- ========================================================================= --}}
    <div id="tab-irigasi" class="agriculture-tab-content">
        <section class="data-card">
            <div class="data-header">
                <div>
                    <h2>Status Suplai &amp; Rekomendasi Pengairan Lahan</h2>
                    <p>Status ketersediaan air dan rekomendasi rotasi pengairan berselang (AWD) per lahan pertanian</p>
                </div>
                <span style="font-size:12px; font-weight:700; color:#166534; background:#e8f5e9; padding:4px 12px; border-radius:20px;">
                    {{ count($irrigationAlerts ?? []) }} Lahan Dipantau
                </span>
            </div>

            <div style="padding: 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px;">
                @forelse($irrigationAlerts ?? [] as $alert)
                    <div style="border: 1px solid {{ $alert['status_color'] }}33; background: {{ $alert['bg_color'] }}; border-radius: 12px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                <strong style="font-size: 14px; color: #0f172a;">{{ $alert['farm_name'] }}</strong>
                                <span style="font-size: 10.5px; font-weight: 750; color: {{ $alert['status_color'] }}; background: #ffffff; padding: 3px 9px; border-radius: 12px; border: 1px solid {{ $alert['status_color'] }}44;">
                                    {{ $alert['level_label'] }}
                                </span>
                            </div>
                            <p style="font-size: 11.5px; color: #475569; margin: 0 0 10px 0;">
                                Pemilik: <strong>{{ $alert['farmer_name'] }}</strong> &bull; Luas: {{ $alert['area_ha'] }} Ha &bull; <span style="text-decoration: underline;">{{ $alert['irrigation_type'] }}</span>
                            </p>
                            <p style="font-size: 12.5px; color: #1e293b; line-height: 1.5; margin: 0;">
                                {{ $alert['message'] }}
                            </p>
                        </div>

                        <div style="margin-top: 14px; padding-top: 10px; border-top: 1px dashed {{ $alert['status_color'] }}44; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 11.5px; font-weight: 600; color: {{ $alert['status_color'] }};">Tindakan Dianjurkan:</span>
                            <a href="{{ route('admin.weather.index') }}" style="font-size: 11.5px; font-weight: 700; color: #ffffff; background: {{ $alert['status_color'] }}; padding: 5px 12px; border-radius: 8px; text-decoration: none;">
                                {{ $alert['action_label'] }} &rarr;
                            </a>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; color: #64748b; font-size: 13.5px; padding: 32px 20px;">
                        Semua sistem pengairan lahan dalam kondisi normal.
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 4: KALKULATOR WAKTU TANAM --}}
    {{-- ========================================================================= --}}
    <div id="tab-kalkulator" class="agriculture-tab-content">
        <section class="data-card">
            <div class="data-header">
                <div>
                    <h2>Kalkulator Estimasi Waktu Tanam &amp; Panen</h2>
                    <p>Hitung rentang waktu tanam ideal, estimasi tanggal panen, dan tahapan pertumbuhan padi berbasis data agroklimat</p>
                </div>
            </div>

            <div style="padding: 24px;">
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)) auto; gap: 16px; align-items:end; margin-bottom:24px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;" for="advisor_farm_id">Pilih Lahan Pertanian</label>
                        <select id="advisor_farm_id" class="pertanian-select" style="background:#fff;">
                            @foreach($farms as $f)
                                <option value="{{ $f->id }}">{{ $f->name }} ({{ $f->area_ha }} Ha)</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;" for="advisor_planned_date">Rencana Tanggal Tanam</label>
                        <input type="date" id="advisor_planned_date" class="pertanian-input" style="background:#fff;" value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                    </div>

                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;" for="advisor_variety_id">Varietas Padi</label>
                        <select id="advisor_variety_id" class="pertanian-select" style="background:#fff;">
                            <option value="1">Inpari 32 HDB (115 Hari)</option>
                            <option value="2">Ciherang (116 Hari)</option>
                            <option value="3">Inpari 42 Agritan GSR (112 Hari)</option>
                            <option value="4">Mekongga (118 Hari)</option>
                        </select>
                    </div>

                    <button type="button" class="btn-submit" style="height:44px; margin:0;" onclick="calculatePlantingRecommendation()">
                        Hitung Waktu Tanam
                    </button>
                </div>

                {{-- Hasil Rekomendasi Kalkulasi --}}
                <div id="advisor-result-box" style="background:#ffffff; border:1px solid #c8e6c9; border-radius:14px; padding:22px; display:none;">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; border-bottom:1px solid #f1f5f9; padding-bottom:16px; margin-bottom:18px;">
                        <div>
                            <span style="font-size:11px; font-weight:750; color:#166534; text-transform:uppercase; letter-spacing:0.04em;">Rentang Waktu Tanam Ideal</span>
                            <h3 id="res-window-label" style="font-size:22px; font-weight:800; color:#0f172a; margin:4px 0 0 0;">01 Nov - 15 Nov 2026</h3>
                        </div>

                        <div style="text-align:right;">
                            <span style="font-size:11px; font-weight:750; color:#64748b; text-transform:uppercase; letter-spacing:0.04em;">Estimasi Tanggal Panen</span>
                            <strong id="res-harvest-date" style="font-size:19px; color:#1b5e20; display:block; margin-top:2px;">10 Maret 2027</strong>
                        </div>
                    </div>

                    <h4 style="font-size:13.5px; font-weight:750; color:#0f172a; margin:0 0 14px 0;">Tahapan Pertumbuhan &amp; Instruksi Lapangan:</h4>
                    <div id="res-milestones-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:14px;"></div>
                </div>
            </div>
        </section>
    </div>

</div>

{{-- ========================================================================= --}}
{{-- MODAL TAMBAH LAHAN BARU DENGAN PETA --}}
{{-- ========================================================================= --}}
<div class="pertanian-modal-backdrop" id="create-farm-modal">
    <div class="pertanian-modal-card">
        <div class="pertanian-modal-header">
            <div>
                <h3 class="pertanian-modal-title">Tambah Lahan Pertanian Baru</h3>
                <p class="pertanian-modal-subtitle">Klik minimal 3-4 titik di peta untuk menentukan batas lahan sawah secara otomatis.</p>
            </div>
            <button type="button" class="pertanian-modal-close" onclick="closeModal('create-farm-modal')">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.agriculture.store') }}">
            @csrf
            <input type="hidden" name="boundary_coordinates" id="create-boundary-coordinates">

            <div class="pertanian-modal-body">

                {{-- Kotak Peta Pemetaan Lahan --}}
                <div class="pertanian-map-picker-box">
                    <div class="map-picker-header">
                        <span>Petunjuk: Klik pada peta untuk menambahkan titik sudut batas sawah</span>
                        <div class="map-picker-controls">
                            <span class="map-picker-badge">Titik: <strong id="create-point-count">0</strong></span>
                            <span class="map-picker-badge">Luas: <strong id="create-calc-area">0.00 ha</strong></span>
                            <button type="button" class="btn-reset-points" onclick="resetMapPoints('create')">Reset Titik</button>
                        </div>
                    </div>
                    <div id="create-map" class="pertanian-map-picker"></div>
                </div>

                <div class="pertanian-modal-grid">
                    <div class="pertanian-modal-grid--full">
                        <label class="pertanian-field-label" for="create-farmer">Pemilik Lahan (Petani)</label>
                        <select id="create-farmer" name="farmer_user_id" class="pertanian-select" required>
                            <option value="">-- Pilih Akun Petani --</option>
                            @foreach($farmers as $f)
                                <option value="{{ $f->id }}" @selected(old('farmer_user_id') == $f->id)>
                                    {{ $f->name }} ({{ $f->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="pertanian-field-label" for="create-name">Nama Lahan Pertanian</label>
                        <input type="text" id="create-name" name="name" class="pertanian-input" value="{{ old('name') }}" placeholder="Contoh: Sawah Blok Selatan" required>
                    </div>

                    <div>
                        <label class="pertanian-field-label" for="create-area">Luas Area (Hektare) <span style="font-weight:500; color:#1b5e20;">(Otomatis)</span></label>
                        <input type="number" step="0.01" min="0.01" id="create-area" name="area_ha" class="pertanian-input" value="{{ old('area_ha') }}" placeholder="Terhitung otomatis dari peta" required>
                    </div>

                    <div>
                        <label class="pertanian-field-label" for="create-lat">Latitude Pusat <span style="font-weight:500; color:#1b5e20;">(Otomatis)</span></label>
                        <input type="number" step="any" id="create-lat" name="latitude" class="pertanian-input" value="{{ old('latitude') }}" placeholder="-7.250000">
                    </div>

                    <div>
                        <label class="pertanian-field-label" for="create-lng">Longitude Pusat <span style="font-weight:500; color:#1b5e20;">(Otomatis)</span></label>
                        <input type="number" step="any" id="create-lng" name="longitude" class="pertanian-input" value="{{ old('longitude') }}" placeholder="112.750000">
                    </div>

                    <div class="pertanian-modal-grid--full">
                        <label class="pertanian-field-label" for="create-irrigation">Tipe Irigasi / Pengairan</label>
                        <select id="create-irrigation" name="irrigation_type" class="pertanian-select" required>
                            <option value="teknis" @selected(old('irrigation_type') === 'teknis')>Irigasi Teknis (Saluran Teratur)</option>
                            <option value="setengah_teknis" @selected(old('irrigation_type') === 'setengah_teknis')>Setengah Teknis</option>
                            <option value="hujan" @selected(old('irrigation_type') === 'hujan')>Sawah Tadah Hujan</option>
                            <option value="swamp" @selected(old('irrigation_type') === 'swamp')>Rawa / Pasang Surut</option>
                            <option value="lainnya" @selected(old('irrigation_type') === 'lainnya')>Lainnya</option>
                        </select>
                    </div>

                    <div class="pertanian-modal-grid--full">
                        <label class="pertanian-field-label" for="create-notes">Catatan Tambahan Lahan</label>
                        <textarea id="create-notes" name="irrigation_notes" class="pertanian-textarea" placeholder="Tuliskan catatan kondisi saluran air, jenis tanah, atau akses jalan...">{{ old('irrigation_notes') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="pertanian-modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('create-farm-modal')">Batal</button>
                <button type="submit" class="btn-submit">Simpan Lahan</button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL EDIT DATA LAHAN --}}
{{-- ========================================================================= --}}
<div class="pertanian-modal-backdrop" id="edit-farm-modal">
    <div class="pertanian-modal-card">
        <div class="pertanian-modal-header">
            <div>
                <h3 class="pertanian-modal-title">Edit Data Lahan Pertanian</h3>
                <p class="pertanian-modal-subtitle">Perbarui data lahan atau sesuaikan titik batas pada peta.</p>
            </div>
            <button type="button" class="pertanian-modal-close" onclick="closeModal('edit-farm-modal')">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>

        <form id="edit-farm-form" method="POST" action="">
            @csrf
            @method('PATCH')
            <input type="hidden" name="boundary_coordinates" id="edit-boundary-coordinates">

            <div class="pertanian-modal-body">

                {{-- Edit Map Picker --}}
                <div class="pertanian-map-picker-box">
                    <div class="map-picker-header">
                        <span>Petunjuk: Geser pin atau klik titik baru untuk menyesuaikan area batas lahan</span>
                        <div class="map-picker-controls">
                            <span class="map-picker-badge">Titik: <strong id="edit-point-count">0</strong></span>
                            <span class="map-picker-badge">Luas: <strong id="edit-calc-area">0.00 ha</strong></span>
                            <button type="button" class="btn-reset-points" onclick="resetMapPoints('edit')">Reset Titik</button>
                        </div>
                    </div>
                    <div id="edit-map" class="pertanian-map-picker"></div>
                </div>

                <div class="pertanian-modal-grid">
                    <div class="pertanian-modal-grid--full">
                        <label class="pertanian-field-label" for="edit-farmer">Pemilik Lahan (Petani)</label>
                        <select id="edit-farmer" name="farmer_user_id" class="pertanian-select" required>
                            <option value="">-- Pilih Akun Petani --</option>
                            @foreach($farmers as $f)
                                <option value="{{ $f->id }}">
                                    {{ $f->name }} ({{ $f->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="pertanian-field-label" for="edit-name">Nama Lahan Pertanian</label>
                        <input type="text" id="edit-name" name="name" class="pertanian-input" required>
                    </div>

                    <div>
                        <label class="pertanian-field-label" for="edit-area">Luas Area (Hektare)</label>
                        <input type="number" step="0.01" min="0.01" id="edit-area" name="area_ha" class="pertanian-input" required>
                    </div>

                    <div>
                        <label class="pertanian-field-label" for="edit-lat">Latitude Pusat</label>
                        <input type="number" step="any" id="edit-lat" name="latitude" class="pertanian-input">
                    </div>

                    <div>
                        <label class="pertanian-field-label" for="edit-lng">Longitude Pusat</label>
                        <input type="number" step="any" id="edit-lng" name="longitude" class="pertanian-input">
                    </div>

                    <div class="pertanian-modal-grid--full">
                        <label class="pertanian-field-label" for="edit-irrigation">Tipe Irigasi / Pengairan</label>
                        <select id="edit-irrigation" name="irrigation_type" class="pertanian-select" required>
                            <option value="teknis">Irigasi Teknis (Saluran Teratur)</option>
                            <option value="setengah_teknis">Setengah Teknis</option>
                            <option value="hujan">Sawah Tadah Hujan</option>
                            <option value="swamp">Rawa / Pasang Surut</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="pertanian-modal-grid--full">
                        <label class="pertanian-field-label" for="edit-notes">Catatan Tambahan Lahan</label>
                        <textarea id="edit-notes" name="irrigation_notes" class="pertanian-textarea"></textarea>
                    </div>
                </div>
            </div>

            <div class="pertanian-modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('edit-farm-modal')">Batal</button>
                <button type="submit" class="btn-submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- Script Logika Interaktif Pertanian --}}
<script>
    // Tab Switcher
    function switchAgricultureTab(tabId, button) {
        document.querySelectorAll('.agriculture-tab-content').forEach(el => el.classList.remove('is-active'));
        document.querySelectorAll('.agriculture-tab-btn').forEach(el => el.classList.remove('is-active'));

        const target = document.getElementById(tabId);
        if (target) target.classList.add('is-active');
        if (button) button.classList.add('is-active');
    }

    // Global Map State
    const mapState = {
        create: { map: null, points: [], markersLayer: null, polygonLayer: null },
        edit: { map: null, points: [], markersLayer: null, polygonLayer: null }
    };

    // Calculate Polygon Area in Hectares
    function calculateAreaInHectares(latLngs) {
        if (latLngs.length < 3) return 0;
        const R = 6378137; // Earth's radius in meters
        let area = 0;
        for (let i = 0; i < latLngs.length; i++) {
            const p1 = latLngs[i];
            const p2 = latLngs[(i + 1) % latLngs.length];
            const lat1 = p1.lat * Math.PI / 180;
            const lat2 = p2.lat * Math.PI / 180;
            const lng1 = p1.lng * Math.PI / 180;
            const lng2 = p2.lng * Math.PI / 180;
            area += (lng2 - lng1) * (2 + Math.sin(lat1) + Math.sin(lat2));
        }
        area = Math.abs(area * R * R / 2);
        return parseFloat((area / 10000).toFixed(2));
    }

    // Calculate Centroid of Points
    function calculateCenter(latLngs) {
        if (!latLngs.length) return { lat: null, lng: null };
        let sumLat = 0;
        let sumLng = 0;
        latLngs.forEach(p => {
            sumLat += p.lat;
            sumLng += p.lng;
        });
        return {
            lat: parseFloat((sumLat / latLngs.length).toFixed(6)),
            lng: parseFloat((sumLng / latLngs.length).toFixed(6))
        };
    }

    function initLeafletMap(mode, initialLat, initialLng, initialPoints = []) {
        const containerId = mode + '-map';
        const state = mapState[mode];

        let pointsToSet = [];
        if (initialPoints && initialPoints.length) {
            pointsToSet = [...initialPoints];
        } else if (initialLat && initialLng) {
            pointsToSet = [{ lat: parseFloat(initialLat), lng: parseFloat(initialLng) }];
        }

        if (state.map) {
            state.map.invalidateSize();
            state.points = pointsToSet;
            updateMapLayers(mode);
            if (state.points.length >= 3) {
                const bounds = L.polygon(state.points.map(p => [p.lat, p.lng])).getBounds();
                state.map.fitBounds(bounds, { padding: [20, 20] });
            } else if (initialLat && initialLng) {
                state.map.setView([parseFloat(initialLat), parseFloat(initialLng)], 15);
            }
            return;
        }

        const defaultLat = initialLat ? parseFloat(initialLat) : -7.250000;
        const defaultLng = initialLng ? parseFloat(initialLng) : 112.750000;

        state.map = L.map(containerId, {
            scrollWheelZoom: false
        }).setView([defaultLat, defaultLng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(state.map);

        state.markersLayer = L.layerGroup().addTo(state.map);
        state.polygonLayer = L.layerGroup().addTo(state.map);

        // Click to add boundary point
        state.map.on('click', function(e) {
            const point = { lat: e.latlng.lat, lng: e.latlng.lng };
            state.points.push(point);
            updateMapLayers(mode);
        });

        // Initialize with initial points
        state.points = pointsToSet;
        updateMapLayers(mode);
        if (state.points.length >= 3) {
            const bounds = L.polygon(state.points.map(p => [p.lat, p.lng])).getBounds();
            state.map.fitBounds(bounds, { padding: [20, 20] });
        }
    }

    function updateMapLayers(mode) {
        const state = mapState[mode];
        state.markersLayer.clearLayers();
        state.polygonLayer.clearLayers();

        state.points.forEach((p, idx) => {
            const iconHtml = `<div style="
                width: 16px;
                height: 16px;
                background: #1b5e20;
                border: 2px solid #ffffff;
                border-radius: 50%;
                box-shadow: 0 2px 6px rgba(0,0,0,0.35);
                cursor: grab;
            "></div>`;

            const customIcon = L.divIcon({
                className: 'custom-drag-marker',
                html: iconHtml,
                iconSize: [16, 16],
                iconAnchor: [8, 8]
            });

            const marker = L.marker([p.lat, p.lng], {
                draggable: true,
                icon: customIcon
            }).bindTooltip('Titik ' + (idx + 1) + ' (Geser untuk sesuaikan)', { direction: 'top', offset: [0, -6] });

            marker.on('dragend', function(event) {
                const newLatLng = event.target.getLatLng();
                state.points[idx] = { lat: newLatLng.lat, lng: newLatLng.lng };
                updateMapLayers(mode);
            });

            state.markersLayer.addLayer(marker);
        });

        if (state.points.length >= 3) {
            const latLngArray = state.points.map(p => [p.lat, p.lng]);
            const polygon = L.polygon(latLngArray, {
                color: '#1b5e20',
                weight: 2,
                fillColor: '#4caf50',
                fillOpacity: 0.35
            });
            state.polygonLayer.addLayer(polygon);
        }

        // Calculate and update fields & indicators
        const areaHa = calculateAreaInHectares(state.points);
        const center = calculateCenter(state.points);

        document.getElementById(mode + '-point-count').textContent = state.points.length;
        document.getElementById(mode + '-calc-area').textContent = areaHa.toFixed(2) + ' ha';

        // Sync boundary coordinates JSON string to hidden input
        const boundaryInput = document.getElementById(mode + '-boundary-coordinates');
        if (boundaryInput) {
            boundaryInput.value = state.points.length ? JSON.stringify(state.points) : '';
        }

        if (center.lat !== null && center.lng !== null) {
            document.getElementById(mode + '-lat').value = center.lat;
            document.getElementById(mode + '-lng').value = center.lng;
        }

        if (areaHa > 0) {
            document.getElementById(mode + '-area').value = areaHa;
        }
    }

    function resetMapPoints(mode) {
        const state = mapState[mode];
        state.points = [];
        if (state.markersLayer) state.markersLayer.clearLayers();
        if (state.polygonLayer) state.polygonLayer.clearLayers();

        document.getElementById(mode + '-point-count').textContent = '0';
        document.getElementById(mode + '-calc-area').textContent = '0.00 ha';
        const boundaryInput = document.getElementById(mode + '-boundary-coordinates');
        if (boundaryInput) boundaryInput.value = '';

        document.getElementById(mode + '-lat').value = '';
        document.getElementById(mode + '-lng').value = '';
        document.getElementById(mode + '-area').value = '';
    }

    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('is-active');
            document.body.style.overflow = 'hidden';
            const body = modal.querySelector('.pertanian-modal-body');
            if (body) body.scrollTop = 0;
        }
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('is-active');
            document.body.style.overflow = '';
        }
    }

    function openCreateFarmModal() {
        openModal('create-farm-modal');
        setTimeout(() => {
            initLeafletMap('create');
            if (mapState.create.map) mapState.create.map.invalidateSize();
        }, 200);
    }

    function openEditFarmModal(farmData) {
        const form = document.getElementById('edit-farm-form');
        form.action = farmData.update_url;

        document.getElementById('edit-farmer').value = farmData.farmer_user_id || '';
        document.getElementById('edit-name').value = farmData.name || '';
        document.getElementById('edit-area').value = farmData.area_ha || '';
        document.getElementById('edit-lat').value = farmData.latitude || '';
        document.getElementById('edit-lng').value = farmData.longitude || '';
        document.getElementById('edit-irrigation').value = farmData.irrigation_type || 'teknis';
        document.getElementById('edit-notes').value = farmData.irrigation_notes || '';

        let initialPoints = [];
        if (farmData.boundary_coordinates) {
            if (typeof farmData.boundary_coordinates === 'string') {
                try { initialPoints = JSON.parse(farmData.boundary_coordinates); } catch(e) {}
            } else if (Array.isArray(farmData.boundary_coordinates)) {
                initialPoints = farmData.boundary_coordinates;
            }
        }

        openModal('edit-farm-modal');

        setTimeout(() => {
            const lat = farmData.latitude ? parseFloat(farmData.latitude) : -7.250000;
            const lng = farmData.longitude ? parseFloat(farmData.longitude) : 112.750000;
            initLeafletMap('edit', lat, lng, initialPoints);
            if (mapState.edit.map) mapState.edit.map.invalidateSize();
        }, 200);
    }

    // Planting Calculator Handler
    function calculatePlantingRecommendation() {
        const farmId = document.getElementById('advisor_farm_id').value;
        const plannedDate = document.getElementById('advisor_planned_date').value;
        const varietyId = document.getElementById('advisor_variety_id').value;

        fetch('/api/v1/planting-calendar/recommend-planting-window', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                farm_id: farmId,
                planned_date: plannedDate,
                variety_id: varietyId
            })
        })
        .then(res => res.json())
        .then(res => {
            if (res.success && res.data) {
                const d = res.data;
                document.getElementById('res-window-label').innerText = d.recommended_planting_window.label;
                document.getElementById('res-harvest-date').innerText = d.estimated_harvest_date;

                const grid = document.getElementById('res-milestones-grid');
                grid.innerHTML = '';

                d.milestones.forEach(m => {
                    grid.innerHTML += `
                        <div style="background:#f8fafc; border:1px solid #e2e8f0; padding:14px; border-radius:10px;">
                            <span style="font-size:10.5px; font-weight:750; color:#166534; text-transform:uppercase;">${m.day_range}</span>
                            <strong style="font-size:13.5px; color:#0f172a; display:block; margin:3px 0;">${m.phase}</strong>
                            <span style="font-size:11.5px; color:#64748b; display:block; margin-bottom:8px;">${m.start_date} - ${m.end_date}</span>
                            <p style="font-size:11.5px; color:#334155; margin:0; line-height:1.45;">${m.action}</p>
                        </div>
                    `;
                });

                document.getElementById('advisor-result-box').style.display = 'block';
            }
        })
        .catch(err => {
            console.error(err);
        });
    }

    // Escape Key Modal Close
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal('create-farm-modal');
            closeModal('edit-farm-modal');
        }
    });

    // Backdrop Click Modal Close
    document.querySelectorAll('.pertanian-modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', function(event) {
            if (event.target === this) {
                closeModal(this.id);
            }
        });
    });
</script>
@endsection
