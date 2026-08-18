@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<link rel="stylesheet" href="{{ asset('css/admin/agriculture.css') }}">

<div class="pertanian-page">
    {{-- Breadcrumb --}}
    <nav class="pertanian-breadcrumb" aria-label="Breadcrumb">
        <span>Admin</span>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="pertanian-breadcrumb-current">Pertanian & Lahan</span>
    </nav>

    {{-- Page Header --}}
    <div class="pertanian-header">
        <div class="pertanian-header-content">
            <h1 class="pertanian-title">Manajemen Pertanian & Lahan</h1>
            <p class="pertanian-description">Pantau data lahan pertanian, titik polygon geospatial lokasi, dan sistem irigasi secara real-time.</p>
        </div>

        <div style="display:flex; gap:10px;">
            <a href="{{ route('admin.knowledge.index') }}" class="btn-add-land" style="background:#e8f5e9; color:#1b5e20; border-color:#81c784;">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>Pusat Pengetahuan Pertanian</span>
            </a>

            <button type="button" class="btn-add-land" onclick="openCreateFarmModal()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Lahan</span>
            </button>
        </div>
    </div>

    {{-- Status Alerts --}}
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

    {{-- Stat KPI Cards --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Total Lahan</p>
                <h3 class="stat-number">{{ number_format($stats['farms'], 0, ',', '.') }}</h3>
                <p class="stat-description">Terdaftar di sistem</p>
            </div>
            <div class="stat-icon stat-icon-green">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2h1.5a2.5 2.5 0 002.5-2.5V7a2 2 0 00-2-2h-2c-.6 0-1.1-.3-1.4-.8l-1.2-2M15 21a6 6 0 100-12 6 6 0 000 12z"/>
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Total Luas Area</p>
                <h3 class="stat-number">{{ number_format($stats['area'], 2, ',', '.') }} <span style="font-size:16px; font-weight:600;">ha</span></h3>
                <p class="stat-description">Total luasan produktif</p>
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
                <p class="stat-label">Total Panen</p>
                <h3 class="stat-number">{{ number_format($stats['harvests'], 0, ',', '.') }}</h3>
                <p class="stat-description">Laporan hasil panen</p>
            </div>
            <div class="stat-icon stat-icon-blue">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- INTERACTIVE PLANTING TIME ADVISOR CALCULATOR --}}
    <section class="data-card" style="border: 2px solid #a7f3d0; background: #f0fdf4; margin-bottom: 28px;">
        <div class="data-header" style="background: #e8f5e9; border-bottom: 1px solid #c8e6c9;">
            <div>
                <h2 style="color: #1b5e20; font-size: 18px;">Kalkulator & Rekomendasi Waktu Tanam Ideal</h2>
                <p style="color: #2e7d32;">Hitung jendela waktu tanam terbaik, estimasi tanggal panen, dan 4 fase siklus pertumbuhan padi berdasarkan BMKG</p>
            </div>
        </div>

        <div style="padding: 24px;">
            <div style="display:grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 16px; align-items:end; margin-bottom:20px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;" for="advisor_farm_id">Pilih Lahan Pertanian</label>
                    <select id="advisor_farm_id" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; background:#fff;">
                        @foreach($farms as $f)
                            <option value="{{ $f->id }}">{{ $f->name }} ({{ $f->area_ha }} Ha)</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;" for="advisor_planned_date">Rencana Tanggal Tanam</label>
                    <input type="date" id="advisor_planned_date" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; background:#fff;" value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                </div>

                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;" for="advisor_variety_id">Varietas Padi</label>
                    <select id="advisor_variety_id" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; background:#fff;">
                        <option value="1">Inpari 32 HDB (115 Hari)</option>
                        <option value="2">Ciherang (116 Hari)</option>
                        <option value="3">Inpari 42 Agritan GSR (112 Hari)</option>
                        <option value="4">Mekongga (118 Hari)</option>
                    </select>
                </div>

                <button type="button" class="btn-filter-submit" style="background:#1b5e20; border-color:#1b5e20; padding:10px 20px;" onclick="calculatePlantingRecommendation()">
                    Hitung Saran Waktu Tanam
                </button>
            </div>

            {{-- Recommendation Result Display --}}
            <div id="advisor-result-box" style="background:#ffffff; border:1px solid #c8e6c9; border-radius:12px; padding:20px; display:none;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; border-bottom:1px solid #f1f5f9; padding-bottom:16px; margin-bottom:16px;">
                    <div>
                        <span style="font-size:11px; font-weight:700; color:#166534; text-transform:uppercase;">Rekomendasi Jendela Waktu Tanam Ideal</span>
                        <h3 id="res-window-label" style="font-size:22px; font-weight:800; color:#0f172a; margin:4px 0 0 0;">01 Nov - 15 Nov 2026</h3>
                    </div>

                    <div style="text-align:right;">
                        <span style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Estimasi Tanggal Panen Presisi</span>
                        <strong id="res-harvest-date" style="font-size:18px; color:#1b5e20; display:block; margin-top:2px;">10 Maret 2027</strong>
                    </div>
                </div>

                <h4 style="font-size:14px; font-weight:700; color:#0f172a; margin:0 0 12px 0;">Milestone Siklus Pertumbuhan & Tindakan Lapangan:</h4>
                <div id="res-milestones-grid" style="display:grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap:12px;"></div>
            </div>
        </div>
    </section>

    <script>
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
                            <div style="background:#f8fafc; border:1px solid #e2e8f0; padding:12px; border-radius:10px;">
                                <span style="font-size:10px; font-weight:700; color:#166534; text-transform:uppercase;">${m.day_range}</span>
                                <strong style="font-size:13px; color:#0f172a; display:block; margin:2px 0;">${m.phase}</strong>
                                <span style="font-size:11px; color:#64748b; display:block; margin-bottom:6px;">${m.start_date} - ${m.end_date}</span>
                                <p style="font-size:11px; color:#334155; margin:0; line-height:1.4;">${m.action}</p>
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
    </script>

    {{-- Lahan Terdaftar Card --}}
    <section class="data-card">
        <div class="data-header">
            <div>
                <h2>Daftar Lahan Pertanian</h2>
                <p>Menampilkan {{ $farms->firstItem() ?? 0 }} - {{ $farms->lastItem() ?? 0 }} dari {{ $farms->total() }} lahan terdaftar</p>
            </div>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="filter-wrapper">
            <form method="GET" action="{{ route('admin.agriculture.index') }}" class="filter-form">
                <div class="search-box">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari nama lahan, catatan irigasi, atau pemilik...">
                </div>

                <select name="irrigation" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Irigasi</option>
                    <option value="teknis" @selected(($filters['irrigation'] ?? '') === 'teknis')>Irigasi Teknis</option>
                    <option value="setengah_teknis" @selected(($filters['irrigation'] ?? '') === 'setengah_teknis')>Setengah Teknis</option>
                    <option value="hujan" @selected(($filters['irrigation'] ?? '') === 'hujan')>Tadah Hujan</option>
                    <option value="swamp" @selected(($filters['irrigation'] ?? '') === 'swamp')>Rawa / Pasang Surut</option>
                    <option value="lainnya" @selected(($filters['irrigation'] ?? '') === 'lainnya')>Lainnya</option>
                </select>

                <button type="submit" class="btn-filter-submit">Filter</button>
                @if(($filters['search'] ?? '') || ($filters['irrigation'] ?? ''))
                    <a href="{{ route('admin.agriculture.index') }}" class="btn-filter-reset">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nama Lahan & Catatan</th>
                        <th>Pemilik Lahan</th>
                        <th>Luas Area</th>
                        <th>Irigasi</th>
                        <th>Koordinat GIS</th>
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
                                'teknis' => 'Teknis',
                                'setengah_teknis' => 'Setengah Teknis',
                                'hujan' => 'Tadah Hujan',
                                'tadah_hujan' => 'Tadah Hujan',
                                'swamp' => 'Rawa',
                                'lainnya' => 'Lainnya',
                            ];
                            $pointsCount = is_array($farm->boundary_coordinates) ? count($farm->boundary_coordinates) : 0;
                        @endphp
                        <tr>
                            <td class="farm-name-cell">
                                <p>{{ $farm->name }}</p>
                                <span>{{ $farm->irrigation_notes ?: 'Tidak ada catatan khusus' }}</span>
                            </td>
                            <td>
                                <div class="farmer-cell">
                                    <div class="farmer-avatar">
                                        {{ strtoupper(substr($farm->farmer?->name ?? 'F', 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="farmer-name">{{ $farm->farmer?->name ?? 'Tanpa Pemilik' }}</p>
                                        <p class="farmer-phone">{{ $farm->farmer?->phone ?? '-' }}</p>
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
                                    <a href="https://maps.google.com/?q={{ $farm->latitude }},{{ $farm->longitude }}" target="_blank" class="coord-badge" title="Buka di Google Maps">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        {{ number_format((float)$farm->latitude, 4) }}, {{ number_format((float)$farm->longitude, 4) }}
                                    </a>
                                @else
                                    <span style="color:#94a3b8; font-size:13px;">Belum diatur</span>
                                @endif
                            </td>
                            <td>
                                @if($pointsCount >= 3)
                                    <span class="irrigation-badge irrigation-teknis" style="font-size:11px;">
                                        {{ $pointsCount }} Titik Boundaries
                                    </span>
                                @elseif($pointsCount > 0)
                                    <span class="irrigation-badge irrigation-hujan" style="font-size:11px;">
                                        {{ $pointsCount }} Titik
                                    </span>
                                @else
                                    <span style="color:#94a3b8; font-size:12px;">Titik tunggal</span>
                                @endif
                            </td>
                            <td>
                                <span style="font-weight:600; color:#0f172a;">{{ $farm->cropSeasons->count() }}</span>
                                <span style="color:#64748b; font-size:12px;"> siklus</span>
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

                                <form method="POST" action="{{ route('admin.agriculture.destroy', $farm) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="btn-delete-land"
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus lahan {{ $farm->name }}?')"
                                    >
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="pertanian-empty">
                                Belum ada data lahan pertanian yang sesuai.
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

    {{-- Musim Tanam Terbaru --}}
    <section class="data-card">
        <div class="data-header">
            <div>
                <h2>Musim Tanam Terbaru</h2>
                <p>10 siklus penanaman padi paling baru yang terdaftar di sistem</p>
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Lahan & Petani</th>
                        <th>Varietas Padi</th>
                        <th>Status Siklus</th>
                        <th>Tanggal Tanam</th>
                        <th>Estimasi Panen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cropSeasons as $season)
                        <tr>
                            <td>
                                <strong>{{ $season->farm?->name ?? '-' }}</strong><br>
                                <small style="color:#64748b;">Pemilik: {{ $season->farm?->farmer?->name ?? '-' }}</small>
                            </td>
                            <td>
                                <span style="font-weight:600; color:#1b5e20;">{{ $season->variety?->name ?? '-' }}</span>
                            </td>
                            <td>
                                @if($season->status === 'active')
                                    <span class="irrigation-badge irrigation-teknis">Aktif</span>
                                @elseif($season->status === 'completed')
                                    <span class="irrigation-badge irrigation-hujan">Selesai</span>
                                @else
                                    <span class="irrigation-badge irrigation-rawa">{{ ucfirst($season->status) }}</span>
                                @endif
                            </td>
                            <td>{{ $season->planting_date ?? $season->planned_planting_date ?? '-' }}</td>
                            <td>{{ $season->estimated_harvest_date ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="pertanian-empty">Belum ada musim tanam di database.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

{{-- MODAL TAMBAH LAHAN --}}
<div class="pertanian-modal-backdrop" id="create-farm-modal">
    <div class="pertanian-modal-card">
        <div class="pertanian-modal-header">
            <div>
                <h3 class="pertanian-modal-title">Tambah Lahan Pertanian</h3>
                <p class="pertanian-modal-subtitle">Klik minimal 4 titik di peta untuk menentukan area polygon batas lahan & kalkulasi otomatis.</p>
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

                {{-- Map Picker Header & Container --}}
                <div class="pertanian-map-picker-box">
                    <div class="map-picker-header">
                        <span>Klik peta (4+ titik) untuk menggambarkan polygon area lahan</span>
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
                            <option value="">-- Pilih Pemilik Akun --</option>
                            @foreach($farmers as $f)
                                <option value="{{ $f->id }}" @selected(old('farmer_user_id') == $f->id)>
                                    {{ $f->name }} ({{ $f->email }}) - {{ ucfirst($f->role) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="pertanian-field-label" for="create-name">Nama Lahan</label>
                        <input type="text" id="create-name" name="name" class="pertanian-input" value="{{ old('name') }}" placeholder="Contoh: Sawah Block A" required>
                    </div>

                    <div>
                        <label class="pertanian-field-label" for="create-area">Luas Area (Hektare) <span style="font-weight:400; color:#1b5e20;">(Auto)</span></label>
                        <input type="number" step="0.01" min="0.01" id="create-area" name="area_ha" class="pertanian-input" value="{{ old('area_ha') }}" placeholder="Kalkulasi otomatis dari peta" required>
                    </div>

                    <div>
                        <label class="pertanian-field-label" for="create-lat">Latitude Pusat <span style="font-weight:400; color:#1b5e20;">(Auto)</span></label>
                        <input type="number" step="any" id="create-lat" name="latitude" class="pertanian-input" value="{{ old('latitude') }}" placeholder="-7.250000">
                    </div>

                    <div>
                        <label class="pertanian-field-label" for="create-lng">Longitude Pusat <span style="font-weight:400; color:#1b5e20;">(Auto)</span></label>
                        <input type="number" step="any" id="create-lng" name="longitude" class="pertanian-input" value="{{ old('longitude') }}" placeholder="112.750000">
                    </div>

                    <div class="pertanian-modal-grid--full">
                        <label class="pertanian-field-label" for="create-irrigation">Tipe Irigasi</label>
                        <select id="create-irrigation" name="irrigation_type" class="pertanian-select" required>
                            <option value="teknis" @selected(old('irrigation_type') === 'teknis')>Irigasi Teknis</option>
                            <option value="setengah_teknis" @selected(old('irrigation_type') === 'setengah_teknis')>Setengah Teknis</option>
                            <option value="hujan" @selected(old('irrigation_type') === 'hujan')>Tadah Hujan</option>
                            <option value="swamp" @selected(old('irrigation_type') === 'swamp')>Rawa / Pasang Surut</option>
                            <option value="lainnya" @selected(old('irrigation_type') === 'lainnya')>Lainnya</option>
                        </select>
                    </div>

                    <div class="pertanian-modal-grid--full">
                        <label class="pertanian-field-label" for="create-notes">Catatan Irigasi / Kondisi Lahan</label>
                        <textarea id="create-notes" name="irrigation_notes" class="pertanian-textarea" placeholder="Detail sumber air, saluran, atau kondisi tanah...">{{ old('irrigation_notes') }}</textarea>
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

{{-- MODAL EDIT LAHAN --}}
<div class="pertanian-modal-backdrop" id="edit-farm-modal">
    <div class="pertanian-modal-card">
        <div class="pertanian-modal-header">
            <div>
                <h3 class="pertanian-modal-title">Edit Lahan Pertanian</h3>
                <p class="pertanian-modal-subtitle">Klik atau geser titik pada peta untuk menyesuaikan kembali area polygon lahan.</p>
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

                {{-- Edit Map Picker Header & Container --}}
                <div class="pertanian-map-picker-box">
                    <div class="map-picker-header">
                        <span>Klik atau geser titik pada peta untuk mengubah polygon lahan</span>
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
                            <option value="">-- Pilih Pemilik Akun --</option>
                            @foreach($farmers as $f)
                                <option value="{{ $f->id }}">
                                    {{ $f->name }} ({{ $f->email }}) - {{ ucfirst($f->role) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="pertanian-field-label" for="edit-name">Nama Lahan</label>
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
                        <label class="pertanian-field-label" for="edit-irrigation">Tipe Irigasi</label>
                        <select id="edit-irrigation" name="irrigation_type" class="pertanian-select" required>
                            <option value="teknis">Irigasi Teknis</option>
                            <option value="setengah_teknis">Setengah Teknis</option>
                            <option value="hujan">Tadah Hujan</option>
                            <option value="swamp">Rawa / Pasang Surut</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="pertanian-modal-grid--full">
                        <label class="pertanian-field-label" for="edit-notes">Catatan Irigasi / Kondisi Lahan</label>
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

<script>
    // Global Map Picker State
    const mapState = {
        create: { map: null, points: [], markersLayer: null, polygonLayer: null },
        edit: { map: null, points: [], markersLayer: null, polygonLayer: null }
    };

    // Calculate Geodesic Polygon Area on Earth in Hectares
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
                fillOpacity: 0.4
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
        }, 200);
    }

    // Close modal on Escape key press
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal('create-farm-modal');
            closeModal('edit-farm-modal');
        }
    });

    // Close modal on backdrop click
    document.querySelectorAll('.pertanian-modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', function(event) {
            if (event.target === this) {
                closeModal(this.id);
            }
        });
    });
</script>
@endsection
