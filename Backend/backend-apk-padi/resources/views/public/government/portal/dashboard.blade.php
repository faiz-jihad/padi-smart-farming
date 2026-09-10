<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/public/government-portal.css') }}?v={{ time() }}">
</head>
<body class="bg-slate-950 text-slate-100 font-['Plus_Jakarta_Sans',sans-serif] antialiased min-h-screen">

    {{-- Screen Only: Top Navigation Bar --}}
    <header class="border-b border-slate-800 bg-slate-900/95 backdrop-blur sticky top-0 z-50 px-6 py-3.5 no-print">
        <div class="max-w-7xl mx-auto flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('government.portal.dashboard') }}" class="flex items-center gap-2 text-white font-black text-lg tracking-tight">
                    🌾 P.A.D.I.
                </a>
                <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 font-bold border border-emerald-500/30">
                    Government Data Portal B2G
                </span>
            </div>

            <div class="flex items-center gap-3">
                {{-- Print Trigger Button --}}
                <button
                    type="button"
                    onclick="window.print()"
                    class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs tracking-wide shadow-md shadow-emerald-900/20 transition flex items-center gap-2 cursor-pointer"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9"/>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                        <rect x="6" y="14" width="12" height="8"/>
                    </svg>
                    <span>Cetak Laporan</span>
                </button>

                {{-- Dedicated Report Preview / PDF Export View --}}
                <a
                    href="{{ route('government.portal.report', request()->query()) }}"
                    target="_blank"
                    class="px-3.5 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold text-xs transition flex items-center gap-1.5"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                    <span>Format Laporan Resmi</span>
                </a>

                {{-- API Docs Link --}}
                <a
                    href="{{ route('government.docs') }}"
                    target="_blank"
                    class="hidden sm:inline-flex px-3 py-2 rounded-lg text-slate-400 hover:text-white text-xs font-semibold transition"
                >
                    Dokumentasi API &rarr;
                </a>

                {{-- Logout Form --}}
                <form action="{{ route('government.portal.logout') }}" method="POST" class="inline">
                    @csrf
                    <button
                        type="submit"
                        class="px-3 py-2 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer"
                        title="Keluar dari sesi portal"
                    >
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- Printable Formal Kop Surat / Institutional Header (Visible only on @media print) --}}
    <div class="print-only-header">
        <div class="print-header-title">P.A.D.I. SMART FARMING</div>
        <div class="print-header-subtitle">LAPORAN DATA PERTANIAN TERPADU &mdash; GOVERNMENT B2G</div>
        <div class="print-header-meta">
            <strong>Instansi Resmi:</strong> {{ $subscription->agency_name }} &bull;
            <strong>Penanggung Jawab (PIC):</strong> {{ $subscription->pic_name }} &bull;
            <strong>Paket:</strong> {{ $subscription->plan_name }} &bull;
            <strong>Status:</strong> AKTIF
        </div>
        <div class="print-header-meta" style="margin-top: 4pt;">
            <strong>Periode Laporan:</strong>
            {{ $filters['start_date'] ? \Carbon\Carbon::parse($filters['start_date'])->isoFormat('D MMMM Y') : 'Semua Data' }}
            sampai
            {{ $filters['end_date'] ? \Carbon\Carbon::parse($filters['end_date'])->isoFormat('D MMMM Y') : 'Saat Ini' }}
            &bull;
            <strong>Dicetak Pada:</strong> {{ now()->isoFormat('D MMMM Y, HH:mm') }} WIB
        </div>
    </div>

    {{-- Main Content Container --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 space-y-8 portal-container">

        {{-- Flash Status Alert --}}
        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs sm:text-sm flex items-center justify-between no-print">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white">&times;</button>
            </div>
        @endif

        {{-- Subscription Identity Card (Official Institutional Banner) --}}
        <div class="portal-card p-6 border-l-4 border-l-emerald-500 bg-gradient-to-r from-slate-900 via-slate-800/80 to-slate-900 shadow-xl">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[11px] font-bold uppercase tracking-wider mb-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        LANGGANAN RESMI AKTIF
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                        {{ $subscription->agency_name }}
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1">
                        PIC: <strong class="text-slate-200">{{ $subscription->pic_name }}</strong> ({{ $subscription->pic_phone }}) &bull;
                        Paket: <strong class="text-emerald-400">{{ $subscription->plan_name }}</strong>
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-4 text-xs">
                    <div class="bg-slate-800/80 border border-slate-700/80 rounded-xl px-4 py-3 text-center min-w-[130px]">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Masa Berlaku</span>
                        <span class="text-white font-black text-sm block mt-0.5">
                            {{ $subscription->expires_at ? $subscription->expires_at->isoFormat('D MMMM Y') : '-' }}
                        </span>
                    </div>

                    <div class="bg-slate-800/80 border border-slate-700/80 rounded-xl px-4 py-3 text-center min-w-[110px]">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Sisa Aktif</span>
                        <span class="text-emerald-400 font-black text-sm block mt-0.5">
                            {{ $subscription->remaining_days }} Hari
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Bar (Screen only) --}}
        <div class="portal-card p-5 portal-filter-box no-print">
            <form action="{{ route('government.portal.dashboard') }}" method="GET" class="space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2 border-b border-slate-700/60 pb-3">
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-300">
                        <svg class="w-4 h-4 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                        </svg>
                        <span>Filter Data &amp; Laporan</span>
                    </div>

                    @if (request()->hasAny(['start_date', 'end_date', 'region', 'commodity', 'activity_type', 'disease']))
                        <a href="{{ route('government.portal.dashboard') }}" class="text-[11px] font-semibold text-rose-400 hover:text-rose-300 transition">
                            &times; Reset Filter
                        </a>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 mb-1">Tanggal Mulai</label>
                        <input
                            type="date"
                            name="start_date"
                            value="{{ $filters['start_date'] }}"
                            class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:border-emerald-500"
                        >
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 mb-1">Tanggal Akhir</label>
                        <input
                            type="date"
                            name="end_date"
                            value="{{ $filters['end_date'] }}"
                            class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:border-emerald-500"
                        >
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 mb-1">Wilayah / Daerah</label>
                        <input
                            type="text"
                            name="region"
                            value="{{ $filters['region'] }}"
                            placeholder="Kabupaten/Kecamatan"
                            class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:border-emerald-500"
                        >
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 mb-1">Komoditas</label>
                        <input
                            type="text"
                            name="commodity"
                            value="{{ $filters['commodity'] }}"
                            placeholder="Varietas (Ciherang, dll)"
                            class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:border-emerald-500"
                        >
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 mb-1">Jenis Aktivitas</label>
                        <input
                            type="text"
                            name="activity_type"
                            value="{{ $filters['activity_type'] }}"
                            placeholder="Tanam, Panen, Pupuk"
                            class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:border-emerald-500"
                        >
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 mb-1">Penyakit Tanaman</label>
                        <input
                            type="text"
                            name="disease"
                            value="{{ $filters['disease'] }}"
                            placeholder="Blast, Tungro, Hawar"
                            class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:border-emerald-500"
                        >
                    </div>
                </div>

                <div class="flex justify-end pt-1">
                    <button
                        type="submit"
                        class="px-5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs tracking-wide shadow-md transition flex items-center gap-1.5 cursor-pointer"
                    >
                        <span>Terapkan Filter</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Section: Ringkasan Pertanian / KPI Cards --}}
        <div>
            <h2 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
                <span>Ringkasan Pertanian Terpadu</span>
            </h2>

            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 print-kpi-grid">
                <div class="portal-card p-4 text-center print-kpi-box">
                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider print-kpi-label">Total Petani</span>
                    <span class="text-xl sm:text-2xl font-black text-white block mt-1 print-kpi-val">{{ number_format($overview['total_farmers']) }}</span>
                </div>

                <div class="portal-card p-4 text-center print-kpi-box">
                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider print-kpi-label">Total Lahan</span>
                    <span class="text-xl sm:text-2xl font-black text-white block mt-1 print-kpi-val">{{ number_format($overview['total_farms']) }}</span>
                </div>

                <div class="portal-card p-4 text-center print-kpi-box">
                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider print-kpi-label">Aktivitas Tani</span>
                    <span class="text-xl sm:text-2xl font-black text-white block mt-1 print-kpi-val">{{ number_format($overview['total_activities']) }}</span>
                </div>

                <div class="portal-card p-4 text-center print-kpi-box">
                    <span class="text-[10px] uppercase font-bold text-rose-400 tracking-wider print-kpi-label">Deteksi Penyakit</span>
                    <span class="text-xl sm:text-2xl font-black text-rose-400 block mt-1 print-kpi-val">{{ number_format($overview['total_disease_detections']) }}</span>
                </div>

                <div class="portal-card p-4 text-center print-kpi-box">
                    <span class="text-[10px] uppercase font-bold text-emerald-400 tracking-wider print-kpi-label">Lahan Produktif</span>
                    <span class="text-xl sm:text-2xl font-black text-emerald-400 block mt-1 print-kpi-val">{{ number_format($overview['productive_farms']) }}</span>
                </div>

                <div class="portal-card p-4 text-center print-kpi-box">
                    <span class="text-[10px] uppercase font-bold text-amber-400 tracking-wider print-kpi-label">Perlu Perhatian</span>
                    <span class="text-xl sm:text-2xl font-black text-amber-400 block mt-1 print-kpi-val">{{ number_format($overview['need_attention_farms']) }}</span>
                </div>

                <div class="portal-card p-4 text-center print-kpi-box">
                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider print-kpi-label">Data Belum Cukup</span>
                    <span class="text-xl sm:text-2xl font-black text-slate-300 block mt-1 print-kpi-val">{{ number_format($overview['insufficient_data_farms']) }}</span>
                </div>
            </div>
        </div>

        {{-- Section 1: Data Lahan Pertanian & Produktivitas --}}
        <div class="portal-card overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-700/80 flex items-center justify-between flex-wrap gap-2 portal-card-header">
                <div>
                    <h3 class="text-base font-bold text-white portal-card-title">Data Lahan Pertanian &amp; Produktivitas</h3>
                    <p class="text-xs text-slate-400">Daftar hamparan lahan tani, luas area (Ha), dan status kalkulasi hasil panen.</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded bg-slate-800 text-slate-300 border border-slate-700 no-print">
                    Total {{ $productivity->total() }} Data Lahan
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full portal-table">
                    <thead>
                        <tr>
                            <th>Wilayah / Lahan</th>
                            <th>Komoditas</th>
                            <th>Luas (Ha)</th>
                            <th>Total Panen</th>
                            <th>Produktivitas</th>
                            <th>Status Produktivitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($productivity as $farm)
                            <tr>
                                <td>
                                    <span class="font-bold text-white block">{{ $farm['farm_name'] }}</span>
                                    <span class="text-[11px] text-slate-400 block">{{ $farm['region'] }}</span>
                                </td>
                                <td>{{ $farm['commodity'] }}</td>
                                <td>{{ number_format($farm['area_ha'], 2) }} Ha</td>
                                <td>{{ number_format($farm['total_harvest_ton'], 2) }} Ton</td>
                                <td>
                                    <span class="font-bold {{ $farm['productivity_ton_per_ha'] >= 4.5 ? 'text-emerald-400' : 'text-slate-300' }}">
                                        {{ number_format($farm['productivity_ton_per_ha'], 2) }} Ton/Ha
                                    </span>
                                </td>
                                <td>
                                    @if ($farm['status'] === 'PRODUCTIVE')
                                        <span class="px-2.5 py-1 rounded-full bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 text-[10px] font-bold uppercase portal-badge">
                                            Produktif
                                        </span>
                                    @elseif ($farm['status'] === 'NEED_ATTENTION')
                                        <span class="px-2.5 py-1 rounded-full bg-amber-500/15 text-amber-400 border border-amber-500/30 text-[10px] font-bold uppercase portal-badge">
                                            Perlu Perhatian
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full bg-slate-700 text-slate-300 text-[10px] font-bold uppercase portal-badge">
                                            Data Belum Cukup
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-8 text-slate-400">
                                    Tidak ada data lahan pertanian yang sesuai dengan kriteria filter saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($productivity->hasPages())
                <div class="p-4 border-t border-slate-800 pagination-wrapper no-print">
                    {{ $productivity->links() }}
                </div>
            @endif
        </div>

        {{-- Section 2: Aktivitas Budidaya Tani --}}
        <div class="portal-card overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-700/80 flex items-center justify-between flex-wrap gap-2 portal-card-header">
                <div>
                    <h3 class="text-base font-bold text-white portal-card-title">Aktivitas Budidaya Tani</h3>
                    <p class="text-xs text-slate-400">Catatan log kegiatan penanaman, pemupukan, irigasi, dan panen para petani.</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded bg-slate-800 text-slate-300 border border-slate-700 no-print">
                    Total {{ $activities->total() }} Aktivitas
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full portal-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Wilayah</th>
                            <th>Komoditas</th>
                            <th>Jenis Aktivitas</th>
                            <th>Keterangan / Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activities as $act)
                            <tr>
                                <td class="whitespace-nowrap font-mono text-xs">
                                    {{ $act->occurred_at ? $act->occurred_at->isoFormat('D MMM Y') : '-' }}
                                </td>
                                <td>
                                    <span class="text-white block font-medium">
                                        {{ $act->cropSeason?->farm?->regency?->name ?? ($act->cropSeason?->farm?->province?->name ?? 'Wilayah Terdaftar') }}
                                    </span>
                                    <span class="text-[11px] text-slate-400 block">
                                        {{ $act->cropSeason?->farm?->district?->name ? 'Kec. ' . $act->cropSeason->farm->district->name : '' }}
                                    </span>
                                </td>
                                <td>{{ $act->cropSeason?->variety?->name ?? 'Padi Sawah' }}</td>
                                <td>
                                    <span class="px-2 py-0.5 rounded bg-slate-800 text-emerald-400 border border-slate-700 text-xs font-semibold uppercase portal-badge">
                                        {{ $act->type }}
                                    </span>
                                </td>
                                <td>{{ $act->notes ?: 'Aktivitas budidaya tercatat.' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-8 text-slate-400">
                                    Tidak ada data aktivitas tani yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($activities->hasPages())
                <div class="p-4 border-t border-slate-800 pagination-wrapper no-print">
                    {{ $activities->links() }}
                </div>
            @endif
        </div>

        {{-- Section 3: Laporan Deteksi & Diagnosis Penyakit Tanaman AI --}}
        <div class="portal-card overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-700/80 flex items-center justify-between flex-wrap gap-2 portal-card-header">
                <div>
                    <h3 class="text-base font-bold text-white portal-card-title">Laporan Deteksi Penyakit Pertanian</h3>
                    <p class="text-xs text-slate-400">Hasil pemindaian optik &amp; diagnosis penyakit padi berbasis AI di lapangan.</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded bg-rose-500/10 text-rose-300 border border-rose-500/20 no-print">
                    Total {{ $diseases->total() }} Deteksi
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full portal-table">
                    <thead>
                        <tr>
                            <th>Tanggal Scan</th>
                            <th>Wilayah</th>
                            <th>Komoditas</th>
                            <th>Diagnosis Penyakit</th>
                            <th>Tingkat / Status</th>
                            <th>Rekomendasi Penanganan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($diseases as $scan)
                            <tr>
                                <td class="whitespace-nowrap font-mono text-xs">
                                    {{ $scan->scanned_at ? $scan->scanned_at->isoFormat('D MMM Y, HH:mm') : '-' }}
                                </td>
                                <td>
                                    <span class="text-white block font-medium">
                                        {{ $scan->farm?->regency?->name ?? ($scan->farm?->province?->name ?? 'Wilayah Terdaftar') }}
                                    </span>
                                    <span class="text-[11px] text-slate-400 block">
                                        {{ $scan->farm?->name }}
                                    </span>
                                </td>
                                <td>Padi Sawah</td>
                                <td>
                                    <span class="font-bold text-rose-400 block">
                                        {{ $scan->predicted_class ?: 'Tidak teridentifikasi' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase portal-badge
                                        {{ $scan->quality_status === 'healthy' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30' }}">
                                        {{ $scan->quality_status ?: 'Terdeteksi' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-slate-300 text-xs line-clamp-2">
                                        {{ $scan->recommendation?->action ?: 'Lakukan isolasi dan penanganan segera sesuai panduan PPL.' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-8 text-slate-400">
                                    Tidak ada laporan deteksi penyakit yang tercatat sesuai filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($diseases->hasPages())
                <div class="p-4 border-t border-slate-800 pagination-wrapper no-print">
                    {{ $diseases->links() }}
                </div>
            @endif
        </div>

        {{-- Section 4: Insight Pertanian & Tren Kedinasan --}}
        <div class="portal-card p-6">
            <h3 class="text-base font-bold text-white mb-4 flex items-center gap-2 portal-card-title">
                <svg class="w-4 h-4 text-emerald-400 no-print" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
                </svg>
                <span>Insight Pertanian &amp; Tren Komoditas</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                {{-- Top Activities --}}
                <div class="bg-slate-900/70 rounded-xl p-4 border border-slate-800">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-3">
                        Aktivitas Tani Paling Dominan
                    </span>
                    <div class="space-y-2.5">
                        @forelse ($insights['top_activities'] ?? [] as $topA)
                            <div class="flex items-center justify-between">
                                <span class="text-slate-300 font-medium">{{ $topA['type'] }}</span>
                                <span class="font-bold text-emerald-400 font-mono">{{ number_format($topA['count']) }} kali</span>
                            </div>
                        @empty
                            <p class="text-slate-500 text-xs">Belum ada data agregat aktivitas.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Top Diseases --}}
                <div class="bg-slate-900/70 rounded-xl p-4 border border-slate-800">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-3">
                        Penyakit Tanaman Paling Banyak Terdeteksi
                    </span>
                    <div class="space-y-2.5">
                        @forelse ($insights['top_diseases'] ?? [] as $topD)
                            <div class="flex items-center justify-between">
                                <span class="text-slate-300 font-medium">{{ $topD['disease'] }}</span>
                                <span class="font-bold text-rose-400 font-mono">{{ number_format($topD['count']) }} kasus</span>
                            </div>
                        @empty
                            <p class="text-slate-500 text-xs">Belum ada data penyakit yang terdeteksi.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    </main>

    {{-- Printable Formal Footer (Visible only on @media print) --}}
    <div class="print-only-footer">
        <div>
            <strong>P.A.D.I. Smart Farming &mdash; Government B2G Data Platform</strong><br>
            Dokumen laporan ini sah dan di-generate otomatis melalui sistem verifikasi token kedinasan resmi.
        </div>
        <div style="text-align: right;">
            Halaman 1 / 1<br>
            Kerahasiaan Dokumen: Terbatas (Government Internal)
        </div>
    </div>

    {{-- Screen Only: Footer --}}
    <footer class="border-t border-slate-900 bg-slate-950 px-6 py-6 text-center text-xs text-slate-500 no-print mt-12">
        <p>&copy; {{ date('Y') }} P.A.D.I. Smart Farming. Hak Cipta Dilindungi Undang-Undang.</p>
        <p class="text-[11px] text-slate-600 mt-1">Portal Integrasi Data Pertanian Resmi &mdash; Khusus Instansi Kedinasan Pemerintah Terverifikasi.</p>
    </footer>

</body>
</html>
