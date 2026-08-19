@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/disease.css') }}">

<div class="penyakit-page">
    <div class="penyakit-container">

        {{-- Page Header --}}
        <div class="penyakit-header">
            <div class="penyakit-header-content">
                <p class="penyakit-eyebrow">Admin Panel</p>
                <h1 class="penyakit-title">Laporan Penyakit</h1>
                <p class="penyakit-description">Pantau hasil scan penyakit tanaman padi dan kelola laporan komunitas dari petani.</p>
            </div>

            <div class="penyakit-header-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                    <path d="M12 8v4"/>
                    <path d="M12 16h.01"/>
                </svg>
                <div>
                    <span>Pending Review</span>
                    <strong>{{ number_format($stats['pending_reports'], 0, ',', '.') }} laporan</strong>
                </div>
            </div>
        </div>

        {{-- Status Alerts --}}
        @if(session('status'))
            <div class="penyakit-header-badge" style="width:100%;border-color:#a7f3d0;background:#ecfdf5;margin-bottom:8px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" style="color:#059669">
                    <path d="M9 12l2 2 4-4"/>
                    <circle cx="12" cy="12" r="10"/>
                </svg>
                <div><strong style="color:#059669">{{ session('status') }}</strong></div>
            </div>
        @endif

        @if($errors->any())
            <div class="penyakit-header-badge" style="width:100%;border-color:#fecaca;background:#fef2f2;margin-bottom:8px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" style="color:#dc2626">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 8v4"/>
                    <path d="M12 16h.01"/>
                </svg>
                <div><strong style="color:#dc2626">{{ $errors->first() }}</strong></div>
            </div>
        @endif

        {{-- Stat Cards --}}
        <div class="penyakit-stat-grid">
            <div class="penyakit-stat-card">
                <div class="penyakit-stat-top">
                    <span class="penyakit-stat-label">Total Scan<br>Penyakit</span>
                    <div class="penyakit-stat-icon green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                        </svg>
                    </div>
                </div>
                <div class="penyakit-stat-bottom">
                    <strong>{{ number_format($stats['scans'], 0, ',', '.') }}</strong>
                    <span>Scan terdaftar di sistem</span>
                </div>
            </div>

            <div class="penyakit-stat-card">
                <div class="penyakit-stat-top">
                    <span class="penyakit-stat-label">Laporan<br>Komunitas</span>
                    <div class="penyakit-stat-icon red">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/>
                            <path d="M12 9v4"/>
                            <path d="M12 17h.01"/>
                        </svg>
                    </div>
                </div>
                <div class="penyakit-stat-bottom">
                    <strong>{{ number_format($stats['reported'], 0, ',', '.') }}</strong>
                    <span>Laporan dari petani</span>
                </div>
            </div>

            <div class="penyakit-stat-card">
                <div class="penyakit-stat-top">
                    <span class="penyakit-stat-label">Menunggu<br>Review</span>
                    <div class="penyakit-stat-icon orange">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 6v6l4 2"/>
                        </svg>
                    </div>
                </div>
                <div class="penyakit-stat-bottom">
                    <strong>{{ number_format($stats['pending_reports'], 0, ',', '.') }}</strong>
                    <span>Status pending</span>
                </div>
            </div>

            <div class="penyakit-stat-card">
                <div class="penyakit-stat-top">
                    <span class="penyakit-stat-label">Gambar<br>Valid</span>
                    <div class="penyakit-stat-icon blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12l2 2 4-4"/>
                            <circle cx="12" cy="12" r="10"/>
                        </svg>
                    </div>
                </div>
                <div class="penyakit-stat-bottom">
                    <strong>{{ number_format($stats['valid_images'], 0, ',', '.') }}</strong>
                    <span>Kualitas gambar valid</span>
                </div>
            </div>
        </div>

        {{-- Disease Scans Table --}}
        <section class="penyakit-data-card">
            <div class="penyakit-data-header">
                <h2>Scan Penyakit Tanaman</h2>
                <p>Hasil deteksi penyakit dari scan gambar daun padi oleh petani.</p>
            </div>

            {{-- Search & Filter --}}
            <div class="penyakit-filter-wrapper">
                <form method="GET" action="{{ route('admin.disease.index') }}" class="penyakit-filter-grid">
                    <div class="penyakit-search">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.35-4.35"/>
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari petani atau penyakit...">
                    </div>

                    <select name="quality" onchange="this.form.submit()">
                        <option value="">Semua Kualitas</option>
                        <option value="valid" @selected(request('quality') === 'valid')>Valid</option>
                        <option value="invalid" @selected(request('quality') === 'invalid')>Invalid</option>
                        <option value="blurry" @selected(request('quality') === 'blurry')>Blurry</option>
                    </select>

                    <select name="disease" onchange="this.form.submit()">
                        <option value="">Semua Penyakit</option>
                        @foreach($diseaseClasses as $class)
                            <option value="{{ $class }}" @selected(request('disease') === $class)>{{ $class }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            {{-- Scans Table --}}
            <div class="penyakit-table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>PETANI</th>
                            <th>LAHAN</th>
                            <th>HASIL PREDIKSI</th>
                            <th>CONFIDENCE</th>
                            <th>KUALITAS</th>
                            <th>WAKTU SCAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($scans as $scan)
                            <tr>
                                <td>
                                    <div class="penyakit-farmer">
                                        <div class="penyakit-avatar">{{ strtoupper(substr($scan->farmer?->name ?? '?', 0, 1)) }}</div>
                                        <div>
                                            <p>{{ $scan->farmer?->name ?? '-' }}</p>
                                            <span>ID: {{ $scan->farmer_id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td><p class="penyakit-farm-name">{{ $scan->farm?->name ?? '-' }}</p></td>
                                <td>
                                    <div class="penyakit-result">
                                        <strong>{{ $scan->predicted_class ?? '-' }}</strong>
                                        <span>{{ $scan->model_version ?? '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="confidence-value">
                                        {{ $scan->confidence ? number_format((float) $scan->confidence * 100, 2, ',', '.') . '%' : '-' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge {{ $scan->quality_status === 'valid' ? 'success' : ($scan->quality_status === 'invalid' ? 'danger' : 'warning') }}">
                                        {{ $scan->quality_status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="scan-date">
                                        <strong>{{ $scan->scanned_at?->format('d M Y') ?? '-' }}</strong>
                                        <span>{{ $scan->scanned_at?->format('H:i') ?? '' }}</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" style="text-align:center;padding:40px 24px;color:#94a3b8;font-weight:500;">Belum ada scan penyakit di database.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Scans Pagination --}}
            @if($scans->hasPages())
                <div class="penyakit-pagination-wrapper">
                    <p class="penyakit-pagination-info">
                        Menampilkan <strong>{{ $scans->firstItem() }}–{{ $scans->lastItem() }}</strong> dari <strong>{{ $scans->total() }}</strong> scan
                    </p>
                    <div class="penyakit-pagination">
                        {{ $scans->links() }}
                    </div>
                </div>
            @endif
        </section>

        {{-- Community Reports Table --}}
        <section class="penyakit-data-card" style="margin-top:28px;">
            <div class="penyakit-data-header">
                <h2>Laporan Komunitas</h2>
                <p>Laporan penyakit dari petani yang membutuhkan review dan validasi admin.</p>
            </div>

            <div class="penyakit-table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>PETANI</th>
                            <th>PENYAKIT TERKAIT</th>
                            <th>LOKASI</th>
                            <th>STATUS</th>
                            <th>DILAPORKAN</th>
                            <th class="right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td><strong>#{{ $report->id }}</strong></td>
                                <td>
                                    <div class="penyakit-farmer">
                                        <div class="penyakit-avatar">{{ strtoupper(substr($report->farmer?->name ?? '?', 0, 1)) }}</div>
                                        <div>
                                            <p>{{ $report->farmer?->name ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="penyakit-result">
                                        <strong>{{ $report->scan?->predicted_class ?? '-' }}</strong>
                                        <span>Confidence: {{ $report->scan?->confidence ? number_format((float) $report->scan->confidence * 100, 1, ',', '.') . '%' : '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="scan-date">
                                        <strong>{{ $report->radius_km }} km</strong>
                                        <span>{{ number_format((float) $report->latitude, 4, '.', '') }}, {{ number_format((float) $report->longitude, 4, '.', '') }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge {{ $report->status === 'verified' || $report->status === 'resolved' ? 'success' : ($report->status === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ $report->status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="scan-date">
                                        <strong>{{ $report->reported_at?->format('d M Y') ?? '-' }}</strong>
                                        <span>{{ $report->reported_at?->format('H:i') ?? '' }}</span>
                                    </div>
                                </td>
                                <td class="action-cell">
                                    <form method="POST" action="{{ route('admin.disease.reports.update', $report) }}" style="display:flex;align-items:center;gap:8px;justify-content:flex-end;">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" style="height:34px;padding:0 10px;border:1px solid #dbe3ed;border-radius:8px;font-size:12px;background:#fff;color:#334155;">
                                            @foreach(['pending', 'verified', 'rejected', 'resolved'] as $status)
                                                <option value="{{ $status }}" @selected($report->status === $status)>{{ ucfirst($status) }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="detail-button">Update</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" style="text-align:center;padding:40px 24px;color:#94a3b8;font-weight:500;">Belum ada laporan komunitas di database.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Reports Pagination --}}
            @if($reports->hasPages())
                <div class="penyakit-pagination-wrapper">
                    <p class="penyakit-pagination-info">
                        Menampilkan <strong>{{ $reports->firstItem() }}–{{ $reports->lastItem() }}</strong> dari <strong>{{ $reports->total() }}</strong> laporan
                    </p>
                    <div class="penyakit-pagination">
                        {{ $reports->links() }}
                    </div>
                </div>
            @endif
        </section>

    </div>
</div>
@endsection
