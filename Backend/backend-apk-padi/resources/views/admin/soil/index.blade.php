@extends('layouts.admin')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/admin/operational.css') }}">

    <div class="admin-page">
        <div class="admin-page__header">
            <div>
                <p class="admin-page__eyebrow">Admin</p>
                <h1 class="admin-page__title">Deteksi & Analisis Tanah</h1>
                <p class="admin-page__description">Pantau kualitas hara tanah (N, P, K, pH) dan rekomendasi pemupukan lahan pertanian.</p>
            </div>
            <div class="admin-page__actions">
                <a href="{{ route('admin.soil.create') }}" class="admin-btn">+ Tambah Sampel Tanah</a>
                <a href="{{ route('admin.weather.index') }}" class="admin-btn admin-btn--secondary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/></svg> Data Cuaca
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="admin-alert admin-alert--success">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="admin-alert admin-alert--error">
                {{ session('error') }}
            </div>
        @endif

        <div class="admin-grid">
            <div class="admin-stat">
                <span>Total Sampel Tanah</span>
                <strong>{{ number_format($stats['total_samples'], 0, ',', '.') }}</strong>
            </div>
            <div class="admin-stat">
                <span>Rata-Rata pH Tanah</span>
                <strong>{{ number_format($stats['avg_ph'], 2, ',', '.') }}</strong>
            </div>
            <div class="admin-stat">
                <span>Kondisi Optimal</span>
                <strong style="color: #16a34a;">{{ number_format($stats['optimal_count'], 0, ',', '.') }}</strong>
            </div>
            <div class="admin-stat">
                <span>Kondisi Kritis / Kurang Hara</span>
                <strong style="color: #dc2626;">{{ number_format($stats['critical_count'] + $stats['needs_fertilizer_count'], 0, ',', '.') }}</strong>
            </div>
        </div>

        <section class="admin-card">
            <div class="admin-card__header">
                <div class="admin-card__title">
                    <span>Filter & Pencarian</span>
                    <h2>Riwayat Hasil Analisis Tanah</h2>
                </div>
            </div>
            <form method="GET" action="{{ route('admin.soil.index') }}" class="admin-form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                <div>
                    <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 0.25rem; display: block;">Lahan</label>
                    <select name="farm_id" class="admin-input">
                        <option value="">Semua Lahan</option>
                        @foreach ($farms as $farm)
                            <option value="{{ $farm->id }}" @if ($filters['farm_id'] == $farm->id) selected @endif>
                                {{ $farm->name }} ({{ $farm->farmer?->name ?? 'Tanpa Petani' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 0.25rem; display: block;">Status</label>
                    <select name="status" class="admin-input">
                        <option value="">Semua Status</option>
                        <option value="optimal" @if ($filters['status'] === 'optimal') selected @endif>Optimal</option>
                        <option value="needs_fertilizer" @if ($filters['status'] === 'needs_fertilizer') selected @endif>Butuh Pupuk</option>
                        <option value="warning" @if ($filters['status'] === 'warning') selected @endif>Peringatan</option>
                        <option value="critical" @if ($filters['status'] === 'critical') selected @endif>Kritis</option>
                    </select>
                </div>
                <div>
                    <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 0.25rem; display: block;">Dari Tanggal</label>
                    <input type="date" name="from_date" value="{{ $filters['from_date'] }}" class="admin-input">
                </div>
                <div>
                    <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 0.25rem; display: block;">Hingga Tanggal</label>
                    <input type="date" name="to_date" value="{{ $filters['to_date'] }}" class="admin-input">
                </div>
                <div style="display: flex; align-items: flex-end; gap: 0.5rem;">
                    <button type="submit" class="admin-btn" style="flex: 1;">Filter</button>
                    <a href="{{ route('admin.soil.index') }}" class="admin-btn admin-btn--secondary">Reset</a>
                </div>
            </form>

            <div style="display: flex; justify-content: flex-end; margin-bottom: 1rem; gap: 0.5rem;">
                <form action="{{ route('admin.soil.export') }}" method="POST" style="display: inline;">
                    @csrf
                    <input type="hidden" name="farm_id" value="{{ $filters['farm_id'] }}">
                    <input type="hidden" name="status" value="{{ $filters['status'] }}">
                    <input type="hidden" name="format" value="csv">
                    <button type="submit" class="admin-btn admin-btn--secondary">Export CSV</button>
                </form>
                <form action="{{ route('admin.soil.export') }}" method="POST" style="display: inline;">
                    @csrf
                    <input type="hidden" name="farm_id" value="{{ $filters['farm_id'] }}">
                    <input type="hidden" name="status" value="{{ $filters['status'] }}">
                    <input type="hidden" name="format" value="json">
                    <button type="submit" class="admin-btn admin-btn--secondary">Export JSON</button>
                </form>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Kode Sampel</th>
                            <th>Lahan & Petani</th>
                            <th>pH</th>
                            <th>N - P - K (ppm)</th>
                            <th>Kelembaban</th>
                            <th>Skor Kesehatan</th>
                            <th>Status</th>
                            <th>Tanggal Uji</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detections as $soil)
                            <tr>
                                <td>
                                    <strong>{{ $soil->sample_code }}</strong><br>
                                    <small class="admin-text--muted">{{ ucfirst($soil->soil_type) }}</small>
                                </td>
                                <td>
                                    <strong>{{ $soil->farm?->name ?? '-' }}</strong><br>
                                    <small>{{ $soil->farm?->farmer?->name ?? '-' }}</small>
                                </td>
                                <td>
                                    <span style="font-weight: 700; color: {{ $soil->ph_level < 5.5 ? '#dc2626' : ($soil->ph_level > 7.5 ? '#d97706' : '#16a34a') }};">
                                        {{ number_format($soil->ph_level, 1) }}
                                    </span>
                                </td>
                                <td>
                                    <small>N: {{ number_format($soil->nitrogen_ppm, 0) }} | P: {{ number_format($soil->phosphorus_ppm, 0) }} | K: {{ number_format($soil->potassium_ppm, 0) }}</small>
                                </td>
                                <td>{{ number_format($soil->moisture_percentage, 1) }}%</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <div style="flex: 1; background: #e5e7eb; height: 8px; border-radius: 4px; overflow: hidden; width: 60px;">
                                            <div style="width: {{ $soil->soil_health_score }}%; background: {{ $soil->soil_health_score >= 80 ? '#16a34a' : ($soil->soil_health_score >= 60 ? '#3b82f6' : ($soil->soil_health_score >= 45 ? '#f59e0b' : '#dc2626')) }}; height: 100%;"></div>
                                        </div>
                                        <strong>{{ $soil->soil_health_score }}</strong>
                                    </div>
                                </td>
                                <td>
                                    @if ($soil->soil_status === 'optimal')
                                        <span class="admin-badge" style="background: #dcfce7; color: #15803d;">Optimal</span>
                                    @elseif ($soil->soil_status === 'needs_fertilizer')
                                        <span class="admin-badge" style="background: #dbeafe; color: #1d4ed8;">Butuh Pupuk</span>
                                    @elseif ($soil->soil_status === 'warning')
                                        <span class="admin-badge" style="background: #fef3c7; color: #b45309;">Peringatan</span>
                                    @else
                                        <span class="admin-badge admin-badge--error">Kritis</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $soil->tested_at->format('d M Y H:i') }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('admin.soil.show', $soil) }}" class="admin-link">Detail</a>
                                    <form action="{{ route('admin.soil.destroy', $soil) }}" method="POST" style="display: inline; margin-left: 0.5rem;" onsubmit="return confirm('Yakin ingin menghapus sampel tanah ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-link" style="color: #dc2626;">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="admin-empty">Belum ada data sampel deteksi tanah. Klik tombol "+ Tambah Sampel Tanah" untuk memulai.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="admin-pagination">{{ $detections->withQueryString()->links() }}</div>
        </section>
    </div>
@endsection
