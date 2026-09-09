@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/disease.css') }}">

<div class="penyakit-page">
    <div class="penyakit-container">

        {{-- Page Header --}}
        <div class="penyakit-header">
            <div class="penyakit-header-content">
                <p class="penyakit-eyebrow">Admin Panel</p>
                <h1 class="penyakit-title">Laporan Penyakit & Kasus PPL</h1>
                <p class="penyakit-description">Kelola verifikasi lapangan petugas PPL, pantau siaran radar komunitas, dan audit hasil scan AI petani.</p>
            </div>

            <div class="penyakit-header-badge" style="width:auto;min-width:240px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                    <path d="M12 8v4"/>
                    <path d="M12 16h.01"/>
                </svg>
                <div>
                    <span>Kasus PPL Menunggu</span>
                    <strong>{{ number_format($stats['ppl_pending'] ?? 0, 0, ',', '.') }} kasus aktif</strong>
                </div>
            </div>
        </div>

        {{-- Status Alerts --}}
        @if(session('status'))
            <div class="penyakit-header-badge" style="width:100%;border-color:#a7f3d0;background:#ecfdf5;margin-bottom:16px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" style="color:#059669">
                    <path d="M9 12l2 2 4-4"/>
                    <circle cx="12" cy="12" r="10"/>
                </svg>
                <div><strong style="color:#059669">{{ session('status') }}</strong></div>
            </div>
        @endif

        @if($errors->any())
            <div class="penyakit-header-badge" style="width:100%;border-color:#fecaca;background:#fef2f2;margin-bottom:16px;">
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
            {{-- Stat 1: Kasus Validasi PPL --}}
            <div class="penyakit-stat-card">
                <div class="penyakit-stat-top">
                    <span class="penyakit-stat-label">Validasi Kasus<br>Petugas PPL</span>
                    <div class="penyakit-stat-icon orange">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 11l3 3L22 4"/>
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                        </svg>
                    </div>
                </div>
                <div class="penyakit-stat-bottom">
                    <strong>{{ number_format($stats['ppl_total'] ?? 0, 0, ',', '.') }}</strong>
                    <span>{{ $stats['ppl_pending'] ?? 0 }} pending • {{ $stats['ppl_validated'] ?? 0 }} divalidasi</span>
                </div>
            </div>

            {{-- Stat 2: Laporan Komunitas / Radar --}}
            <div class="penyakit-stat-card">
                <div class="penyakit-stat-top">
                    <span class="penyakit-stat-label">Laporan Radar<br>Komunitas</span>
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
                    <span>{{ $stats['pending_reports'] }} belum diverifikasi</span>
                </div>
            </div>

            {{-- Stat 3: Total Scan Penyakit --}}
            <div class="penyakit-stat-card">
                <div class="penyakit-stat-top">
                    <span class="penyakit-stat-label">Total Diagnosa<br>AI Penyakit</span>
                    <div class="penyakit-stat-icon green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                        </svg>
                    </div>
                </div>
                <div class="penyakit-stat-bottom">
                    <strong>{{ number_format($stats['scans'], 0, ',', '.') }}</strong>
                    <span>Hasil pemindaian daun</span>
                </div>
            </div>

            {{-- Stat 4: Validitas Citra --}}
            <div class="penyakit-stat-card">
                <div class="penyakit-stat-top">
                    <span class="penyakit-stat-label">Kualitas Citra<br>Valid</span>
                    <div class="penyakit-stat-icon blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12l2 2 4-4"/>
                            <circle cx="12" cy="12" r="10"/>
                        </svg>
                    </div>
                </div>
                <div class="penyakit-stat-bottom">
                    <strong>{{ number_format($stats['valid_images'], 0, ',', '.') }}</strong>
                    <span>Lolos segmentasi klorofil</span>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- SECTION 1: KASUS VALIDASI PPL (Laporan Petani ke Petugas Penyuluh)        --}}
        {{-- ========================================================================= --}}
        <section class="penyakit-data-card" style="margin-bottom:32px; border-left: 4px solid #059669;">
            <div class="penyakit-data-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
                <div>
                    <h2 style="display:flex;align-items:center;gap:8px;">
                        <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#059669;"></span>
                        Kasus Validasi Penyuluh (PPL)
                    </h2>
                    <p>Laporan langsung dari petani yang meminta peninjauan lapangan oleh petugas PPL. Pembaruan status di sini otomatis menyinkronkan notifikasi ke aplikasi petani.</p>
                </div>
                <div style="font-size:12px;background:#ecfdf5;color:#065f46;padding:6px 12px;border-radius:8px;font-weight:700;border:1px solid #a7f3d0;">
                    Sinkronisasi Petani: AKTIF 🟢
                </div>
            </div>

            {{-- PPL Filter & Search --}}
            <div class="penyakit-filter-wrapper">
                <form method="GET" action="{{ route('admin.disease.index') }}" class="penyakit-filter-grid">
                    <div class="penyakit-search">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.35-4.35"/>
                        </svg>
                        <input type="text" name="ppl_search" value="{{ request('ppl_search') }}" placeholder="Cari petani, sawah, penyakit, atau penyuluh...">
                    </div>

                    <select name="ppl_status" onchange="this.form.submit()">
                        <option value="">Semua Status PPL</option>
                        <option value="pending" @selected(request('ppl_status') === 'pending')>⏳ Menunggu Validasi (Pending)</option>
                        <option value="validated" @selected(request('ppl_status') === 'validated')>✅ Divalidasi Lapangan</option>
                        <option value="needs_revisit" @selected(request('ppl_status') === 'needs_revisit')>🔄 Perlu Kunjungan Ulang</option>
                        <option value="rejected" @selected(request('ppl_status') === 'rejected')>❌ Tidak Terkonfirmasi</option>
                    </select>

                    <div style="display:flex;gap:8px;">
                        <button type="submit" style="flex:1;height:44px;border:none;border-radius:12px;background:#059669;color:#fff;font-weight:700;cursor:pointer;font-size:13px;">
                            Filter Kasus
                        </button>
                        @if(request('ppl_search') || request('ppl_status'))
                            <a href="{{ route('admin.disease.index') }}" style="height:44px;line-height:44px;padding:0 14px;border:1px solid #cbd5e1;border-radius:12px;color:#64748b;text-decoration:none;font-size:12px;font-weight:600;display:inline-block;">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- PPL Validations Table --}}
            <div class="penyakit-table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>KASUS & PETANI</th>
                            <th>LAHAN PERTANIAN</th>
                            <th>INDIKASI AI & KEYAKINAN</th>
                            <th>CATATAN PETANI</th>
                            <th>PETUGAS PPL</th>
                            <th>STATUS</th>
                            <th class="right">TINDAK LANJUT ADMIN & PPL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pplValidations as $validation)
                            @php
                                $scan = $validation->scan;
                                $farmer = $scan?->farmer;
                                $farm = $scan?->farm;
                                $ppl = $validation->ppl;
                            @endphp
                            <tr>
                                <td>
                                    <div class="penyakit-farmer">
                                        <div class="penyakit-avatar" style="background:#d1fae5;color:#065f46;font-weight:800;">
                                            #{{ $validation->id }}
                                        </div>
                                        <div>
                                            <p style="font-weight:700;color:#0f172a;">{{ $farmer?->name ?? 'Petani #'.$scan?->farmer_id }}</p>
                                            <span style="font-size:11px;color:#64748b;">
                                                HP: {{ $farmer?->phone ?? '-' }} • {{ $validation->created_at?->diffForHumans() ?? '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong style="color:#1e293b;font-size:13px;">{{ $farm?->name ?? 'Sawah Terdaftar' }}</strong>
                                    <span style="display:block;font-size:11px;color:#64748b;">ID Lahan: {{ $scan?->farm_id ?? '-' }}</span>
                                </td>
                                <td>
                                    <div class="penyakit-result">
                                        <strong style="color:#047857;font-size:13.5px;">{{ $scan?->predicted_class ?? '-' }}</strong>
                                        <span style="color:#059669;font-weight:600;">
                                            Akurasi: {{ $scan?->confidence ? number_format((float) $scan->confidence * 100, 1, ',', '.') . '%' : '-' }}
                                        </span>
                                        @if($scan?->image_url)
                                            <a href="{{ $scan->image_url }}" target="_blank" style="font-size:11px;color:#0284c7;text-decoration:underline;margin-top:2px;">
                                                Lihat Foto Daun ↗
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td style="max-width:200px;">
                                    @if(!empty($validation->notes))
                                        <div style="font-size:12px;color:#334155;background:#f8fafc;padding:6px 10px;border-radius:8px;border:1px solid #e2e8f0;line-height:1.4;">
                                            "{{ Str::limit($validation->notes, 100) }}"
                                        </div>
                                    @else
                                        <span style="color:#94a3b8;font-size:12px;">(Tanpa catatan)</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ppl)
                                        <div style="font-size:12.5px;font-weight:700;color:#0f172a;">{{ $ppl->name }}</div>
                                        <span style="font-size:11px;color:#64748b;">{{ ucfirst($ppl->role ?? 'Penyuluh') }}</span>
                                    @else
                                        <span style="display:inline-block;padding:3px 8px;border-radius:6px;background:#fef3c7;color:#92400e;font-size:11px;font-weight:700;">
                                            Belum Ditugaskan
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $badgeClass = match($validation->status) {
                                            'validated' => 'success',
                                            'rejected' => 'danger',
                                            'needs_revisit' => 'warning',
                                            default => 'warning'
                                        };
                                        $badgeText = match($validation->status) {
                                            'validated' => 'Divalidasi',
                                            'rejected' => 'Tidak Terkonfirmasi',
                                            'needs_revisit' => 'Kunjungan Ulang',
                                            default => 'Menunggu Validasi'
                                        };
                                    @endphp
                                    <span class="status-badge {{ $badgeClass }}">
                                        {{ $badgeText }}
                                    </span>
                                </td>
                                <td class="action-cell">
                                    <form method="POST" action="{{ route('admin.disease.ppl-validations.update', $validation) }}" style="display:flex;flex-direction:column;gap:6px;align-items:flex-end;">
                                        @csrf
                                        @method('PATCH')
                                        <div style="display:flex;align-items:center;gap:6px;">
                                            {{-- Dropdown Status --}}
                                            <select name="status" style="height:32px;padding:0 8px;border:1px solid #cbd5e1;border-radius:8px;font-size:12px;background:#fff;color:#1e293b;font-weight:600;">
                                                <option value="pending" @selected($validation->status === 'pending')>⏳ Pending</option>
                                                <option value="validated" @selected($validation->status === 'validated')>✅ Validasi</option>
                                                <option value="needs_revisit" @selected($validation->status === 'needs_revisit')>🔄 Kunjungan Ulang</option>
                                                <option value="rejected" @selected($validation->status === 'rejected')>❌ Tolak</option>
                                            </select>

                                            {{-- Dropdown Assign PPL --}}
                                            <select name="ppl_id" style="height:32px;max-width:140px;padding:0 8px;border:1px solid #cbd5e1;border-radius:8px;font-size:11.5px;background:#fff;color:#1e293b;">
                                                <option value="">Pilih Penyuluh...</option>
                                                @foreach($extensionOfficers as $officer)
                                                    <option value="{{ $officer->id }}" @selected($validation->ppl_id === $officer->id)>
                                                        {{ $officer->name }} ({{ $officer->role === 'admin' ? 'Admin' : 'PPL' }})
                                                    </option>
                                                @endforeach
                                            </select>

                                            <button type="submit" class="detail-button" style="background:#059669;color:#fff;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:700;">
                                                Update
                                            </button>
                                        </div>
                                        <input type="text" name="notes" value="{{ $validation->notes }}" placeholder="Tulis catatan tindak lanjut..." style="width:280px;height:28px;padding:0 8px;border:1px solid #e2e8f0;border-radius:6px;font-size:11px;color:#475569;">
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align:center;padding:40px 24px;color:#94a3b8;font-weight:500;">
                                    Belum ada kasus laporan validasi PPL di sistem.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PPL Pagination --}}
            @if($pplValidations->hasPages())
                <div class="penyakit-pagination-wrapper">
                    <p class="penyakit-pagination-info">
                        Menampilkan <strong>{{ $pplValidations->firstItem() }}–{{ $pplValidations->lastItem() }}</strong> dari <strong>{{ $pplValidations->total() }}</strong> kasus PPL
                    </p>
                    <div class="penyakit-pagination">
                        {{ $pplValidations->links() }}
                    </div>
                </div>
            @endif
        </section>

        {{-- ========================================================================= --}}
        {{-- SECTION 2: LAPORAN KOMUNITAS (Radar Wabah Penyakit & Peringatan Dini)      --}}
        {{-- ========================================================================= --}}
        <section class="penyakit-data-card" style="margin-bottom:32px;">
            <div class="penyakit-data-header">
                <h2>Laporan Komunitas (Radar Wabah)</h2>
                <p>Laporan siaran radar penyakit dari petani yang menyiarkan peringatan dini dalam radius tertentu. Memperbarui status di sini otomatis menyinkronkan notifikasi ke petani pelapor dan merefresh radar peta.</p>
            </div>

            <div class="penyakit-table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>PETANI</th>
                            <th>PENYAKIT TERKAIT</th>
                            <th>LOKASI & RADIUS</th>
                            <th>STATUS</th>
                            <th>DILAPORKAN</th>
                            <th class="right">AKSI VERIFIKASI</th>
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
                                        <strong style="color:#dc2626;">Radius {{ $report->radius_km }} km</strong>
                                        <span>{{ number_format((float) $report->latitude, 4, '.', '') }}, {{ number_format((float) $report->longitude, 4, '.', '') }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge {{ $report->status === 'verified' || $report->status === 'resolved' ? 'success' : ($report->status === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($report->status) }}
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
                                        <button type="submit" class="detail-button" style="background:#047857;color:#fff;padding:6px 12px;border-radius:8px;">Update</button>
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
                        Menampilkan <strong>{{ $reports->firstItem() }}–{{ $reports->lastItem() }}</strong> dari <strong>{{ $reports->total() }}</strong> laporan radar
                    </p>
                    <div class="penyakit-pagination">
                        {{ $reports->links() }}
                    </div>
                </div>
            @endif
        </section>

        {{-- ========================================================================= --}}
        {{-- SECTION 3: SCAN PENYAKIT TANAMAN (Hasil Diagnosa Citra AI Petani)         --}}
        {{-- ========================================================================= --}}
        <section class="penyakit-data-card">
            <div class="penyakit-data-header">
                <h2>Riwayat Scan Diagnosa Daun Padi</h2>
                <p>Hasil deteksi otomatis penyakit dari scan gambar daun padi oleh petani menggunakan model YOLO & ResNet-50.</p>
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

    </div>
</div>
@endsection
