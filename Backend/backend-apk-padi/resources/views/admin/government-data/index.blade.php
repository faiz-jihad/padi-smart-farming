@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/government-data.css') }}?v={{ time() }}">
@endpush

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/government-data.css') }}?v={{ time() }}">

<div class="b2g-page-container">
    {{-- Breadcrumb --}}
    <nav class="b2g-breadcrumb" aria-label="Breadcrumb">
        <span>Admin</span>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span>Integrasi Data B2G</span>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="b2g-breadcrumb-current">Government Data Center</span>
    </nav>

    {{-- Page Header --}}
    <div class="b2g-page-header">
        <div class="b2g-header-title-box">
            <div class="b2g-header-tag">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                Portal Data B2G Terpadu
            </div>
            <h1 class="b2g-page-title">Government Data Center (B2G)</h1>
            <p class="b2g-page-subtitle">Pusat integrasi data pertanian, deteksi penyakit tanaman AI, kalibrasi produktivitas panen, dan manajemen persetujuan akses instansi kedinasan (Dinas Pertanian).</p>
        </div>
        <div class="b2g-page-actions">
            <a href="{{ route('government.index') }}" target="_blank" class="b2g-btn b2g-btn--secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                    <polyline points="15 3 21 3 21 9"/>
                    <line x1="10" y1="14" x2="21" y2="3"/>
                </svg>
                Portal Publik B2G
            </a>
            <a href="{{ route('government.docs') }}" target="_blank" class="b2g-btn b2g-btn--primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                </svg>
                Dokumentasi API
            </a>
        </div>
    </div>

    {{-- Flash & Generated Token Modal/Banner --}}
    @if (session('generated_token'))
        <div class="b2g-token-banner">
            <div class="b2g-token-banner__header">
                <div class="b2g-token-badge">
                    <svg class="w-3.5 h-3.5 inline mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                    TOKEN AKSES 6 DIGIT DIAKTIFKAN
                </div>
                <h3 class="b2g-token-title">Persetujuan Berhasil untuk {{ session('generated_agency') }}</h3>
                <p class="b2g-token-desc">Berikan token akses 6 digit berikut kepada perwakilan resmi instansi kedinasan. Paket <strong>{{ session('generated_plan') }}</strong> aktif selama {{ session('generated_days') }} hari (berlaku hingga <strong>{{ session('generated_expiry') }}</strong>).</p>
            </div>
            <div class="b2g-token-display">
                <div class="b2g-token-code-wrap">
                    <span class="b2g-token-code">{{ session('generated_token') }}</span>
                    <button type="button" class="b2g-token-copy-btn" onclick="navigator.clipboard.writeText('{{ session('generated_token') }}'); this.innerText = '✓ Tersalin!'; setTimeout(() => this.innerText = 'Salin Token', 2000);">
                        <svg class="w-4 h-4 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        Salin Token
                    </button>
                </div>
                <div class="b2g-token-warning">
                    ⚠️ Harap catat atau salin token ini sekarang. Demi privasi dan keamanan sistem, kode lengkap tidak akan ditampilkan kembali.
                </div>
            </div>
        </div>
    @endif

    @if (session('status'))
        <div class="b2g-alert b2g-alert--success">
            <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    {{-- KPI Metric Cards --}}
    <div class="b2g-kpi-grid">
        <div class="b2g-kpi-card">
            <div class="b2g-kpi-meta">
                <span class="b2g-kpi-label">Total Registrasi</span>
                <span class="b2g-kpi-value">{{ number_format($kpis['total_subscriptions']) }}</span>
            </div>
            <div class="b2g-kpi-icon b2g-icon-blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>

        <div class="b2g-kpi-card">
            <div class="b2g-kpi-meta">
                <span class="b2g-kpi-label">Langganan Aktif</span>
                <span class="b2g-kpi-value text-emerald">{{ number_format($kpis['active']) }}</span>
            </div>
            <div class="b2g-kpi-icon b2g-icon-green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>

        <div class="b2g-kpi-card">
            <div class="b2g-kpi-meta">
                <span class="b2g-kpi-label">Menunggu Approval</span>
                <span class="b2g-kpi-value text-amber">{{ number_format($kpis['pending_approval']) }}</span>
            </div>
            <div class="b2g-kpi-icon b2g-icon-amber">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>

        <div class="b2g-kpi-card">
            <div class="b2g-kpi-meta">
                <span class="b2g-kpi-label">Menunggu Bayar</span>
                <span class="b2g-kpi-value" style="color:#64748b;">{{ number_format($kpis['pending_payment']) }}</span>
            </div>
            <div class="b2g-kpi-icon b2g-icon-gray">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </div>
        </div>

        <div class="b2g-kpi-card">
            <div class="b2g-kpi-meta">
                <span class="b2g-kpi-label">Total Revenue B2G</span>
                <span class="b2g-kpi-value" style="color:#6366f1;">Rp {{ number_format($kpis['total_revenue'], 0, ',', '.') }}</span>
            </div>
            <div class="b2g-kpi-icon b2g-icon-purple">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <div class="b2g-tabs">
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'subscriptions']) }}" class="b2g-tab-link {{ ($filters['tab'] ?? 'subscriptions') === 'subscriptions' ? 'is-active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Daftar Langganan &amp; Token
        </a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'activities']) }}" class="b2g-tab-link {{ ($filters['tab'] ?? '') === 'activities' ? 'is-active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            Riwayat Aktivitas Tani
        </a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'diseases']) }}" class="b2g-tab-link {{ ($filters['tab'] ?? '') === 'diseases' ? 'is-active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Pemantauan Penyakit
        </a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'productivity']) }}" class="b2g-tab-link {{ ($filters['tab'] ?? '') === 'productivity' ? 'is-active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            Produktivitas Pertanian
        </a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'insights']) }}" class="b2g-tab-link {{ ($filters['tab'] ?? '') === 'insights' ? 'is-active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            Agregasi &amp; Insight Dinas
        </a>
    </div>

    {{-- TAB 1: SUBSCRIPTIONS --}}
    @if (($filters['tab'] ?? 'subscriptions') === 'subscriptions')
        <div class="b2g-card">
            <div class="b2g-card__header">
                <div>
                    <h2 class="b2g-card__title">Kelola Langganan Instansi Pemerintah</h2>
                    <p class="b2g-card__subtitle">Verifikasi status transaksi Midtrans, persetujuan admin kedinasan, dan penerbitan token akses 6 digit.</p>
                </div>
            </div>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('admin.government-data.index') }}" class="b2g-filter-bar">
                <input type="hidden" name="tab" value="subscriptions">
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Cari instansi, email, atau PIC..." class="b2g-input" style="flex:1; min-width:240px;">
                <select name="status" class="b2g-select">
                    <option value="">Semua Status</option>
                    <option value="PENDING_PAYMENT" {{ $filters['status'] === 'PENDING_PAYMENT' ? 'selected' : '' }}>Pending Payment</option>
                    <option value="PENDING_APPROVAL" {{ $filters['status'] === 'PENDING_APPROVAL' ? 'selected' : '' }}>Pending Approval (Sudah Bayar)</option>
                    <option value="ACTIVE" {{ $filters['status'] === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                    <option value="EXPIRED" {{ $filters['status'] === 'EXPIRED' ? 'selected' : '' }}>Expired</option>
                    <option value="REVOKED" {{ $filters['status'] === 'REVOKED' ? 'selected' : '' }}>Revoked</option>
                </select>
                <button type="submit" class="b2g-btn b2g-btn--primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    Filter
                </button>
                <a href="{{ route('admin.government-data.index', ['tab' => 'subscriptions']) }}" class="b2g-btn b2g-btn--secondary">Reset</a>
            </form>

            {{-- Table --}}
            <div class="b2g-table-wrap">
                <table class="b2g-table">
                    <thead>
                        <tr>
                            <th>Instansi &amp; PIC</th>
                            <th>Kontak Resmi</th>
                            <th>Paket &amp; Biaya</th>
                            <th>Status Bayar</th>
                            <th>Status Akun</th>
                            <th>Token (Masked)</th>
                            <th>Masa Berlaku</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subscriptions as $sub)
                            <tr>
                                <td>
                                    <div class="font-bold text-slate-900" style="font-size:14px; color:#0f172a;">{{ $sub->agency_name }}</div>
                                    <div style="font-size:12px; color:#64748b; margin-top:2px;">PIC: <span style="font-weight:600; color:#334155;">{{ $sub->pic_name }}</span></div>
                                </td>
                                <td>
                                    <div style="font-size:12.5px; color:#1e293b; font-weight:500;">{{ $sub->agency_email }}</div>
                                    <div style="font-size:12px; color:#64748b; margin-top:2px;">{{ $sub->pic_phone }}</div>
                                </td>
                                <td>
                                    <div style="font-weight:600; color:#0f172a;">{{ $sub->plan_name }} ({{ $sub->plan_days }} Hari)</div>
                                    <div style="font-size:13px; font-weight:800; color:#166534; margin-top:2px;">Rp {{ number_format($sub->amount, 0, ',', '.') }}</div>
                                </td>
                                <td>
                                    @php
                                        $payment = $sub->latestPayment;
                                        $pStatus = $payment?->transaction_status ?? 'unpaid';
                                    @endphp
                                    @if ($pStatus === 'settlement' || $pStatus === 'capture')
                                        <span class="b2g-badge b2g-badge--success">PAID (LUNAS)</span>
                                    @elseif ($pStatus === 'pending')
                                        <span class="b2g-badge b2g-badge--warning">MENUNGGU BAYAR</span>
                                    @else
                                        <span class="b2g-badge b2g-badge--neutral">{{ strtoupper($pStatus) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($sub->status === 'ACTIVE' && $sub->isActive())
                                        <span class="b2g-badge b2g-badge--success">AKTIF</span>
                                    @elseif ($sub->status === 'PENDING_APPROVAL')
                                        <span class="b2g-badge b2g-badge--warning">MENUNGGU APPROVAL</span>
                                    @elseif ($sub->status === 'PENDING_PAYMENT')
                                        <span class="b2g-badge b2g-badge--neutral">BELUM BAYAR</span>
                                    @elseif ($sub->status === 'REVOKED')
                                        <span class="b2g-badge b2g-badge--danger">DICABUT (REVOKED)</span>
                                    @elseif ($sub->status === 'EXPIRED' || ($sub->expires_at && $sub->expires_at->isPast()))
                                        <span class="b2g-badge b2g-badge--danger">KEDALUWARSA</span>
                                    @else
                                        <span class="b2g-badge b2g-badge--neutral">{{ $sub->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($sub->token_preview)
                                        <code class="b2g-code">{{ $sub->token_preview }}</code>
                                    @else
                                        <span style="font-size:12px; color:#94a3b8;">Belum terbit</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($sub->isActive())
                                        <div style="font-size:12.5px; font-weight:700; color:#166534;">{{ $sub->remaining_days }} hari tersisa</div>
                                        <div style="font-size:11.5px; color:#64748b; margin-top:2px;">s/d {{ $sub->expires_at?->format('d M Y') }}</div>
                                    @elseif ($sub->expires_at)
                                        <div style="font-size:12px; color:#64748b;">Expired: {{ $sub->expires_at->format('d M Y') }}</div>
                                    @else
                                        <span style="font-size:12px; color:#94a3b8;">-</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div style="display:inline-flex; align-items:center; gap:6px;">
                                        <a href="{{ route('admin.government-data.show', $sub->id) }}" class="b2g-btn b2g-btn--sm b2g-btn--secondary" title="Lihat Detail">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            Detail
                                        </a>

                                        @if ($sub->status === 'PENDING_PAYMENT')
                                            <form method="POST" action="{{ route('admin.government-data.confirm-payment', $sub->id) }}" onsubmit="return confirm('Konfirmasi pembayaran manual untuk {{ $sub->agency_name }}? Status akan berubah menjadi Menunggu Approval.');" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="b2g-btn b2g-btn--sm" style="background:#0284c7; color:#ffffff;" title="Konfirmasi Pembayaran Manual">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                    Konfirmasi Bayar
                                                </button>
                                            </form>
                                        @endif

                                        @if ($sub->status === 'PENDING_APPROVAL')
                                            <form method="POST" action="{{ route('admin.government-data.approve', $sub->id) }}" onsubmit="return confirm('Setujui dan terbitkan token 6 digit untuk {{ $sub->agency_name }}?');" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="b2g-btn b2g-btn--sm b2g-btn--primary" title="Approve & Generate Token">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                    Approve &amp; Token
                                                </button>
                                            </form>
                                        @endif

                                        @if ($sub->isActive())
                                            <form method="POST" action="{{ route('admin.government-data.revoke', $sub->id) }}" onsubmit="return confirm('Yakin ingin mencabut (revoke) akses token {{ $sub->agency_name }}? Token akan langsung dibatalkan.');" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="b2g-btn b2g-btn--sm b2g-btn--danger">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                                    Revoke
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="b2g-empty-state">
                                        <svg class="b2g-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                            <polyline points="14 2 14 8 20 8"/>
                                            <line x1="16" y1="13" x2="8" y2="13"/>
                                            <line x1="16" y1="17" x2="8" y2="17"/>
                                        </svg>
                                        <h4 class="b2g-empty-title">Belum Ada Pendaftaran Langganan</h4>
                                        <p class="b2g-empty-desc">Data pendaftaran instansi kedinasan yang masuk akan tercatat dan ditampilkan di sini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="b2g-pagination-wrap">
                {{ $subscriptions->links() }}
            </div>
        </div>
    @endif

    {{-- TAB 2: ACTIVITIES --}}
    @if (($filters['tab'] ?? '') === 'activities')
        <div class="b2g-card">
            <div class="b2g-card__header">
                <div>
                    <h2 class="b2g-card__title">Riwayat Aktivitas Pertanian (Database Source of Truth)</h2>
                    <p class="b2g-card__subtitle">Log pencatatan kegiatan budidaya petani (penanaman, pemupukan, penyiraman, pengendalian hama, panen) yang tersedia untuk B2G API.</p>
                </div>
            </div>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('admin.government-data.index') }}" class="b2g-filter-bar">
                <input type="hidden" name="tab" value="activities">
                <input type="date" name="start_date" value="{{ $filters['start_date'] }}" class="b2g-input" title="Tanggal Mulai">
                <input type="date" name="end_date" value="{{ $filters['end_date'] }}" class="b2g-input" title="Tanggal Akhir">
                <input type="text" name="region" value="{{ $filters['region'] }}" placeholder="Wilayah (Kabupaten/Kecamatan)..." class="b2g-input" style="flex:1; min-width:180px;">
                <input type="text" name="commodity" value="{{ $filters['commodity'] }}" placeholder="Komoditas..." class="b2g-input">
                <input type="text" name="activity_type" value="{{ $filters['activity_type'] }}" placeholder="Jenis Kegiatan..." class="b2g-input">
                <button type="submit" class="b2g-btn b2g-btn--primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    Filter
                </button>
                <a href="{{ route('admin.government-data.index', ['tab' => 'activities']) }}" class="b2g-btn b2g-btn--secondary">Reset</a>
            </form>

            <div class="b2g-table-wrap">
                <table class="b2g-table">
                    <thead>
                        <tr>
                            <th>Waktu &amp; Tanggal</th>
                            <th>Jenis Aktivitas</th>
                            <th>Lahan Pertanian</th>
                            <th>Wilayah</th>
                            <th>Komoditas</th>
                            <th>Catatan / Keterangan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activities as $act)
                            @php
                                $farm = $act->cropSeason?->farm;
                                $variety = $act->cropSeason?->variety;
                                $date = $act->occurred_at ? \Carbon\Carbon::parse($act->occurred_at) : null;
                            @endphp
                            <tr>
                                <td>
                                    <div style="font-weight:600; color:#0f172a;">{{ $date ? $date->translatedFormat('d M Y') : '-' }}</div>
                                    <div style="font-size:12px; color:#64748b; margin-top:2px;">{{ $date ? $date->format('H:i') . ' WIB' : '-' }}</div>
                                </td>
                                <td>
                                    <span class="b2g-badge b2g-badge--neutral" style="font-weight:700;">{{ ucfirst($act->type ?? 'Kegiatan Tani') }}</span>
                                </td>
                                <td>
                                    <div style="font-weight:700; color:#0f172a;">{{ $farm?->name ?? 'Lahan Terdaftar' }}</div>
                                    <div style="font-size:12px; color:#64748b; margin-top:2px;">{{ $farm?->area_ha ?? 0 }} Hektar</div>
                                </td>
                                <td>
                                    <div style="font-weight:600; color:#0f172a; font-size:13px;">{{ $farm?->regency?->name ?? '-' }}</div>
                                    <div style="font-size:11.5px; color:#64748b; margin-top:2px;">{{ $farm?->district?->name ?? '' }} {{ $farm?->village?->name ? ', Ds. ' . $farm->village->name : '' }}</div>
                                </td>
                                <td>
                                    <span class="b2g-badge b2g-badge--success">{{ $variety?->name ? 'Padi ' . $variety->name : 'Padi Sawah' }}</span>
                                </td>
                                <td>
                                    <span style="font-size:12.5px; color:#475569;">{{ $act->notes ?: 'Pencatatan rutin budidaya lahan.' }}</span>
                                </td>
                                <td>
                                    <span class="b2g-badge b2g-badge--success">TERCATAT</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="b2g-empty-state">
                                        <svg class="b2g-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <circle cx="12" cy="12" r="10"/>
                                            <line x1="12" y1="8" x2="12" y2="12"/>
                                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                                        </svg>
                                        <h4 class="b2g-empty-title">Tidak Ada Aktivitas Ditemukan</h4>
                                        <p class="b2g-empty-desc">Tidak ada data aktivitas pertanian yang cocok dengan filter yang dipilih.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="b2g-pagination-wrap">
                {{ $activities->links() }}
            </div>
        </div>
    @endif

    {{-- TAB 3: DISEASES --}}
    @if (($filters['tab'] ?? '') === 'diseases')
        <div class="b2g-card">
            <div class="b2g-card__header">
                <div>
                    <h2 class="b2g-card__title">Pemantauan Penyakit Tanaman (Disease Monitoring)</h2>
                    <p class="b2g-card__subtitle">Rekapitulasi kasus deteksi penyakit daun padi berbasis AI dan validasi lapangan oleh petugas PPL.</p>
                </div>
            </div>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('admin.government-data.index') }}" class="b2g-filter-bar">
                <input type="hidden" name="tab" value="diseases">
                <input type="date" name="start_date" value="{{ $filters['start_date'] }}" class="b2g-input" title="Tanggal Mulai">
                <input type="date" name="end_date" value="{{ $filters['end_date'] }}" class="b2g-input" title="Tanggal Akhir">
                <input type="text" name="region" value="{{ $filters['region'] }}" placeholder="Wilayah..." class="b2g-input" style="flex:1; min-width:180px;">
                <input type="text" name="disease" value="{{ $filters['disease'] }}" placeholder="Nama Penyakit..." class="b2g-input">
                <button type="submit" class="b2g-btn b2g-btn--primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    Filter
                </button>
                <a href="{{ route('admin.government-data.index', ['tab' => 'diseases']) }}" class="b2g-btn b2g-btn--secondary">Reset</a>
            </form>

            <div class="b2g-table-wrap">
                <table class="b2g-table">
                    <thead>
                        <tr>
                            <th>Waktu Deteksi</th>
                            <th>Diagnosa Penyakit</th>
                            <th>Tingkat Keyakinan (AI)</th>
                            <th>Lahan &amp; Wilayah</th>
                            <th>Kondisi Tanaman</th>
                            <th>Rekomendasi Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($diseases as $scan)
                            @php
                                $date = $scan->scanned_at ? \Carbon\Carbon::parse($scan->scanned_at) : ($scan->created_at ? \Carbon\Carbon::parse($scan->created_at) : null);
                            @endphp
                            <tr>
                                <td>
                                    <div style="font-weight:600; color:#0f172a;">{{ $date ? $date->translatedFormat('d M Y') : '-' }}</div>
                                    <div style="font-size:12px; color:#64748b; margin-top:2px;">{{ $date ? $date->format('H:i') . ' WIB' : '-' }}</div>
                                </td>
                                <td>
                                    <div style="font-weight:700; color:#0f172a; font-size:14px;">{{ $scan->predicted_class ?? 'Pemeriksaan Daun' }}</div>
                                    <div style="font-size:12px; color:#64748b; margin-top:2px;">Komoditas: Padi Sawah</div>
                                </td>
                                <td>
                                    @if ($scan->confidence !== null)
                                        <div style="font-weight:800; color:#166534; font-size:14px;">{{ round($scan->confidence * 100, 1) }}%</div>
                                        <div style="font-size:11px; color:#94a3b8;">Confidence Score</div>
                                    @else
                                        <span style="font-size:12px; color:#94a3b8;">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight:600; color:#0f172a;">{{ $scan->farm?->name ?? 'Lahan Terdaftar' }}</div>
                                    <div style="font-size:12px; color:#64748b; margin-top:2px;">{{ $scan->farm?->regency?->name ?? 'Wilayah Terdaftar' }}</div>
                                </td>
                                <td>
                                    @if ($scan->quality_status === 'healthy')
                                        <span class="b2g-badge b2g-badge--success">SEHAT</span>
                                    @else
                                        <span class="b2g-badge b2g-badge--danger">TERINFEKSI</span>
                                    @endif
                                </td>
                                <td>
                                    <span style="font-size:12.5px; color:#475569;">{{ $scan->recommendation?->action ?? 'Lakukan isolasi dan sanitasi petak sawah.' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="b2g-empty-state">
                                        <svg class="b2g-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                        </svg>
                                        <h4 class="b2g-empty-title">Tidak Ada Laporan Penyakit</h4>
                                        <p class="b2g-empty-desc">Tidak ada data kasus penyakit tanaman yang cocok dengan filter yang dipilih.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="b2g-pagination-wrap">
                {{ $diseases->links() }}
            </div>
        </div>
    @endif

    {{-- TAB 4: PRODUCTIVITY --}}
    @if (($filters['tab'] ?? '') === 'productivity')
        <div class="b2g-card">
            <div class="b2g-card__header">
                <div>
                    <h2 class="b2g-card__title">Evaluasi Produktivitas Pertanian</h2>
                    <p class="b2g-card__subtitle">Perhitungan rasio hasil panen terhadap luas lahan (ton/ha) dengan klasifikasi status objektif berbasis data panen aktual.</p>
                </div>
            </div>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('admin.government-data.index') }}" class="b2g-filter-bar">
                <input type="hidden" name="tab" value="productivity">
                <input type="text" name="region" value="{{ $filters['region'] }}" placeholder="Wilayah (Kabupaten/Kecamatan)..." class="b2g-input" style="flex:1; min-width:200px;">
                <input type="text" name="commodity" value="{{ $filters['commodity'] }}" placeholder="Komoditas..." class="b2g-input">
                <button type="submit" class="b2g-btn b2g-btn--primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    Filter
                </button>
                <a href="{{ route('admin.government-data.index', ['tab' => 'productivity']) }}" class="b2g-btn b2g-btn--secondary">Reset</a>
            </form>

            <div class="b2g-table-wrap">
                <table class="b2g-table">
                    <thead>
                        <tr>
                            <th>Lahan Pertanian</th>
                            <th>Wilayah</th>
                            <th>Komoditas</th>
                            <th>Luas Lahan</th>
                            <th>Total Panen</th>
                            <th>Produktivitas (Yield)</th>
                            <th>Status Klasifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($productivity as $prod)
                            <tr>
                                <td>
                                    <div style="font-weight:700; color:#0f172a;">{{ $prod['farm_name'] }}</div>
                                    <div style="font-size:12px; color:#64748b; margin-top:2px;">ID Lahan: #{{ $prod['farm_id'] }}</div>
                                </td>
                                <td>
                                    <div style="font-weight:600; color:#0f172a;">{{ $prod['region'] }}</div>
                                </td>
                                <td>
                                    <span class="b2g-badge b2g-badge--success">{{ $prod['commodity'] }}</span>
                                </td>
                                <td>
                                    <div style="font-weight:600; color:#0f172a;">{{ number_format($prod['area_ha'], 2) }} ha</div>
                                </td>
                                <td>
                                    <div style="font-weight:800; color:#0f172a;">{{ number_format($prod['total_harvest_ton'], 2) }} Ton</div>
                                    <div style="font-size:11.5px; color:#64748b; margin-top:2px;">{{ $prod['harvest_records'] }} catatan panen</div>
                                </td>
                                <td>
                                    <div style="font-weight:900; color:#4338ca; font-size:14px;">{{ number_format($prod['productivity_ton_per_ha'], 2) }} Ton/Ha</div>
                                </td>
                                <td>
                                    @if ($prod['status'] === 'PRODUCTIVE')
                                        <span class="b2g-badge b2g-badge--success">PRODUKTIF (&ge; 4.5 T/Ha)</span>
                                    @elseif ($prod['status'] === 'NEED_ATTENTION')
                                        <span class="b2g-badge b2g-badge--warning">PERLU ATENSI (&lt; 4.5 T/Ha)</span>
                                    @else
                                        <span class="b2g-badge b2g-badge--neutral">BELUM PANEN</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="b2g-empty-state">
                                        <svg class="b2g-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <line x1="18" y1="20" x2="18" y2="10"/>
                                            <line x1="12" y1="20" x2="12" y2="4"/>
                                            <line x1="6" y1="20" x2="6" y2="14"/>
                                        </svg>
                                        <h4 class="b2g-empty-title">Data Produktivitas Belum Tersedia</h4>
                                        <p class="b2g-empty-desc">Tidak ada data evaluasi panen yang cocok dengan parameter pencarian.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="b2g-pagination-wrap">
                {{ $productivity->links() }}
            </div>
        </div>
    @endif

    {{-- TAB 5: INSIGHTS --}}
    @if (($filters['tab'] ?? '') === 'insights')
        <div style="display:flex; flex-direction:column; gap:24px;">
            {{-- 3 Metric Cards --}}
            <div class="b2g-grid-3">
                <div class="b2g-metric-card">
                    <div class="b2g-metric-label">Tingkat Produktivitas Wilayah</div>
                    <div class="b2g-metric-value text-emerald">{{ $insights['productive_rate_pct'] }}%</div>
                    <p style="font-size:12.5px; color:#64748b; margin:0; line-height:1.5;">Lahan memenuhi standar produktivitas nasional (&ge; 4.5 ton/ha).</p>
                    <div class="b2g-progress-track">
                        <div class="b2g-progress-fill" style="width: {{ $insights['productive_rate_pct'] }}%; background:#16a34a;"></div>
                    </div>
                </div>

                <div class="b2g-metric-card">
                    <div class="b2g-metric-label">Lahan Perlu Pendampingan</div>
                    <div class="b2g-metric-value text-amber">{{ $insights['need_attention_rate_pct'] }}%</div>
                    <p style="font-size:12.5px; color:#64748b; margin:0; line-height:1.5;">Lahan dengan panen rendah atau intensitas serangan penyakit tinggi.</p>
                    <div class="b2g-progress-track">
                        <div class="b2g-progress-fill" style="width: {{ $insights['need_attention_rate_pct'] }}%; background:#d97706;"></div>
                    </div>
                </div>

                <div class="b2g-metric-card">
                    <div class="b2g-metric-label">Masa Tanam Berjalan / Belum Panen</div>
                    <div class="b2g-metric-value" style="color:#64748b;">{{ $insights['insufficient_data_rate_pct'] }}%</div>
                    <p style="font-size:12.5px; color:#64748b; margin:0; line-height:1.5;">Lahan dalam fase vegetatif/generatif sebelum panen.</p>
                    <div class="b2g-progress-track">
                        <div class="b2g-progress-fill" style="width: {{ $insights['insufficient_data_rate_pct'] }}%; background:#94a3b8;"></div>
                    </div>
                </div>
            </div>

            {{-- 2 Breakdown Cards --}}
            <div class="b2g-grid-2">
                {{-- Top Activities --}}
                <div class="b2g-card" style="margin-bottom:0;">
                    <div class="b2g-card__header">
                        <h3 class="b2g-card__title" style="display:flex; align-items:center; gap:8px;">
                            <svg class="w-4 h-4 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22V8"/><path d="M5 12c0-3 2-5 7-5s7 2 7 5"/><path d="M5 12c0 4 3 7 7 7s7-3 7-7"/><path d="M12 8c-2-3-1-5 0-6 1 1 1 3 0 6Z"/></svg>
                            Aktivitas Pertanian Paling Sering Dicatat
                        </h3>
                    </div>
                    <div style="padding:20px; display:flex; flex-direction:column; gap:10px;">
                        @forelse ($insights['top_activities'] as $act)
                            <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 16px; background:#f8fafc; border-radius:10px; border:1px solid #f1f5f9;">
                                <span style="font-size:13.5px; font-weight:700; color:#1e293b;">{{ $act['type'] }}</span>
                                <span class="b2g-badge b2g-badge--success" style="font-size:12px;">{{ $act['count'] }} kegiatan</span>
                            </div>
                        @empty
                            <p style="font-size:13px; color:#94a3b8; text-align:center; padding:24px 0; margin:0;">Belum ada riwayat aktivitas tani tercatat.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Top Diseases --}}
                <div class="b2g-card" style="margin-bottom:0;">
                    <div class="b2g-card__header">
                        <h3 class="b2g-card__title" style="display:flex; align-items:center; gap:8px;">
                            <svg class="w-4 h-4 text-rose" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                            Penyakit Tanaman Paling Sering Terdeteksi
                        </h3>
                    </div>
                    <div style="padding:20px; display:flex; flex-direction:column; gap:10px;">
                        @forelse ($insights['top_diseases'] as $dis)
                            <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 16px; background:#f8fafc; border-radius:10px; border:1px solid #f1f5f9;">
                                <span style="font-size:13.5px; font-weight:700; color:#1e293b;">{{ $dis['disease'] }}</span>
                                <span class="b2g-badge b2g-badge--danger" style="font-size:12px;">{{ $dis['count'] }} kasus</span>
                            </div>
                        @empty
                            <p style="font-size:13px; color:#94a3b8; text-align:center; padding:24px 0; margin:0;">Tidak ada laporan wabah penyakit aktif.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
