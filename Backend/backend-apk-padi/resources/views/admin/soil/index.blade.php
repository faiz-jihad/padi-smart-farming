@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/soil.css') }}">

<div class="soil-page">
    {{-- Breadcrumb --}}
    <nav class="soil-breadcrumb" aria-label="Breadcrumb">
        <span>Admin</span>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="soil-breadcrumb-current">Deteksi & Analisis Tanah</span>
    </nav>

    {{-- Page Header --}}
    <div class="soil-header">
        <div class="soil-header-content">
            <h1 class="soil-title">Deteksi & Analisis Kualitas Tanah</h1>
            <p class="soil-description">Pantau data parameter hara tanah (N, P, K, pH, kelembaban) dan evaluasi pemupukan lahan pertanian secara akurat.</p>
        </div>

        <div class="soil-header-actions">
            <a href="{{ route('admin.soil.create') }}" class="btn-soil-action btn-soil-primary">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Sampel Tanah</span>
            </a>

            <a href="{{ route('admin.weather.index') }}" class="btn-soil-action">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2h1.5a2.5 2.5 0 002.5-2.5V7a2 2 0 00-2-2h-2c-.6 0-1.1-.3-1.4-.8l-1.2-2M15 21a6 6 0 100-12 6 6 0 000 12z" />
                </svg>
                <span>Data Cuaca</span>
            </a>
        </div>
    </div>

    {{-- Status Alerts --}}
    @if(session('status'))
        <div style="background:#dcfce7; border:1px solid #86efac; color:#166534; padding:12px 18px; border-radius:10px; margin-bottom:20px; font-size:13px; font-weight:600; display:flex; align-items:center; justify-content:space-between;" id="alert-status">
            <span>{{ session('status') }}</span>
            <button type="button" style="background:none; border:none; color:#166534; cursor:pointer; font-weight:700;" onclick="document.getElementById('alert-status').remove()">✕</button>
        </div>
    @endif

    @if(session('error'))
        <div style="background:#0f172a; border:1px solid #334155; color:#ffffff; padding:12px 18px; border-radius:10px; margin-bottom:20px; font-size:13px; font-weight:600; display:flex; align-items:center; justify-content:space-between;" id="alert-error">
            <span>{{ session('error') }}</span>
            <button type="button" style="background:none; border:none; color:#ffffff; cursor:pointer; font-weight:700;" onclick="document.getElementById('alert-error').remove()">✕</button>
        </div>
    @endif

    {{-- Stat KPI Cards --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Total Sampel Tanah</p>
                <h3 class="stat-number">{{ number_format($stats['total_samples'], 0, ',', '.') }}</h3>
                <p class="stat-description">Pengujian terdaftar</p>
            </div>
            <div class="stat-icon stat-icon-primary">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L5.6 15.12a2 2 0 00-1.144.12l-1.344.603V19a2 2 0 002 2h14a2 2 0 002-2v-3.033l-.628-.539z" />
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Rata-Rata pH Tanah</p>
                <h3 class="stat-number">{{ number_format($stats['avg_ph'], 2, ',', '.') }}</h3>
                <p class="stat-description">Tingkat keasaman lahan</p>
            </div>
            <div class="stat-icon">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Kondisi Optimal</p>
                <h3 class="stat-number">{{ number_format($stats['optimal_count'], 0, ',', '.') }}</h3>
                <p class="stat-description">Subur & seimbang</p>
            </div>
            <div class="stat-icon stat-icon-primary">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Perlu Penanganan</p>
                <h3 class="stat-number">{{ number_format($stats['critical_count'] + $stats['needs_fertilizer_count'] + $stats['warning_count'], 0, ',', '.') }}</h3>
                <p class="stat-description">Kurang hara / Peringatan</p>
            </div>
            <div class="stat-icon">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Main Data Table Card --}}
    <section class="data-card">
        <div class="data-header">
            <div>
                <h2>Riwayat Hasil Analisis Tanah</h2>
                <p>Menampilkan {{ $detections->firstItem() ?? 0 }} - {{ $detections->lastItem() ?? 0 }} dari {{ $detections->total() }} sampel terdaftar</p>
            </div>

            <div class="export-group">
                <form action="{{ route('admin.soil.export') }}" method="POST" style="display:inline;">
                    @csrf
                    <input type="hidden" name="farm_id" value="{{ $filters['farm_id'] }}">
                    <input type="hidden" name="status" value="{{ $filters['status'] }}">
                    <input type="hidden" name="format" value="csv">
                    <button type="submit" class="btn-export">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="13" height="13">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Export CSV</span>
                    </button>
                </form>

                <form action="{{ route('admin.soil.export') }}" method="POST" style="display:inline;">
                    @csrf
                    <input type="hidden" name="farm_id" value="{{ $filters['farm_id'] }}">
                    <input type="hidden" name="status" value="{{ $filters['status'] }}">
                    <input type="hidden" name="format" value="json">
                    <button type="submit" class="btn-export">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="13" height="13">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Export JSON</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="filter-wrapper">
            <form method="GET" action="{{ route('admin.soil.index') }}" class="filter-form">
                <div class="search-box">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari kode sampel, nama lahan, atau petani...">
                </div>

                <select name="farm_id" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Lahan</option>
                    @foreach ($farms as $farm)
                        <option value="{{ $farm->id }}" @selected($filters['farm_id'] == $farm->id)>
                            {{ $farm->name }} ({{ $farm->farmer?->name ?? 'Tanpa Petani' }})
                        </option>
                    @endforeach
                </select>

                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="optimal" @selected($filters['status'] === 'optimal')>Optimal</option>
                    <option value="needs_fertilizer" @selected($filters['status'] === 'needs_fertilizer')>Butuh Pupuk</option>
                    <option value="warning" @selected($filters['status'] === 'warning')>Peringatan</option>
                    <option value="critical" @selected($filters['status'] === 'critical')>Kritis</option>
                </select>

                <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="filter-date" title="Dari Tanggal">
                <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="filter-date" title="Hingga Tanggal">

                <button type="submit" class="btn-filter-submit">Filter</button>
                @if(($filters['search'] ?? '') || ($filters['farm_id'] ?? '') || ($filters['status'] ?? '') || ($filters['from_date'] ?? '') || ($filters['to_date'] ?? ''))
                    <a href="{{ route('admin.soil.index') }}" class="btn-filter-reset">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-wrapper">
            <table class="soil-table">
                <thead>
                    <tr>
                        <th style="padding-left: 24px;">Kode Sampel & Jenis</th>
                        <th>Lahan & Petani</th>
                        <th>pH Level</th>
                        <th>Kandungan N - P - K</th>
                        <th>Kelembaban</th>
                        <th>Skor Kesehatan</th>
                        <th>Status Evaluasi</th>
                        <th>Tanggal Uji</th>
                        <th style="text-align: right; padding-right: 24px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detections as $soil)
                        @php
                            $phClass = $soil->ph_level < 5.5 ? 'ph-critical' : ($soil->ph_level > 7.5 ? 'ph-warning' : 'ph-optimal');
                        @endphp
                        <tr>
                            <td class="sample-cell" style="padding-left: 24px;">
                                <p>{{ $soil->sample_code }}</p>
                                <span>Jenis: {{ ucfirst($soil->soil_type) }}</span>
                            </td>
                            <td>
                                <strong style="font-weight:700; color:#0f172a;">{{ $soil->farm?->name ?? '-' }}</strong><br>
                                <span style="font-size:12px; color:#64748b;">Pemilik: {{ $soil->farm?->farmer?->name ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="ph-badge {{ $phClass }}">
                                    {{ number_format($soil->ph_level, 1) }}
                                </span>
                            </td>
                            <td>
                                <div class="npk-pill">
                                    <span>N: {{ number_format($soil->nitrogen_ppm, 0) }}</span>
                                    <span>P: {{ number_format($soil->phosphorus_ppm, 0) }}</span>
                                    <span>K: {{ number_format($soil->potassium_ppm, 0) }}</span>
                                </div>
                            </td>
                            <td>
                                <span style="font-weight:700; color:#0f172a;">{{ number_format($soil->moisture_percentage, 1) }}%</span>
                            </td>
                            <td>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <div class="health-progress-bar">
                                        <div class="health-progress-fill" style="width: {{ $soil->soil_health_score }}%;"></div>
                                    </div>
                                    <span style="font-weight:800; font-size:12px; color:#166534;">{{ $soil->soil_health_score }}</span>
                                </div>
                            </td>
                            <td>
                                @if ($soil->soil_status === 'optimal')
                                    <span class="soil-status-badge status-optimal">Optimal</span>
                                @elseif ($soil->soil_status === 'needs_fertilizer')
                                    <span class="soil-status-badge status-fertilizer">Butuh Pupuk</span>
                                @elseif ($soil->soil_status === 'warning')
                                    <span class="soil-status-badge status-warning">Peringatan</span>
                                @else
                                    <span class="soil-status-badge status-critical">Kritis</span>
                                @endif
                            </td>
                            <td>
                                <span style="font-size:12px; color:#64748b; font-weight:500;">{{ $soil->tested_at->format('d M Y H:i') }}</span>
                            </td>
                            <td style="text-align: right; padding-right: 24px; white-space:nowrap;">
                                <a href="{{ route('admin.soil.show', $soil) }}" class="btn-action-view">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="13" height="13">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Detail
                                </a>

                                @if(auth()->user()?->hasRole(\App\Enums\UserRole::Admin->value))
                                <form action="{{ route('admin.soil.destroy', $soil) }}" method="POST" style="display:inline; margin-left:4px;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus sampel tanah {{ $soil->sample_code }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action-delete">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="13" height="13">
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
                            <td colspan="9" class="soil-empty">
                                Belum ada data sampel deteksi tanah terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($detections->hasPages())
            <div class="pagination-wrapper">
                {{ $detections->withQueryString()->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
