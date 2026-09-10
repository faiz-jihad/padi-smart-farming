<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm 1.2cm 1.5cm 1.2cm;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
                font-family: 'Times New Roman', Times, serif !important;
                font-size: 10.5pt !important;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-['Plus_Jakarta_Sans',sans-serif] antialiased min-h-screen py-6 sm:py-10">

    {{-- Screen-only toolbar --}}
    <div class="max-w-4xl mx-auto px-4 mb-6 flex items-center justify-between no-print">
        <a href="{{ route('government.portal.dashboard') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 flex items-center gap-1">
            &larr; Kembali ke Dashboard Portal
        </a>

        <div class="flex items-center gap-2">
            <button
                type="button"
                onclick="window.print()"
                class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg text-xs font-bold shadow transition flex items-center gap-1.5 cursor-pointer"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 6 2 18 2 18 9"/>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                    <rect x="6" y="14" width="12" height="8"/>
                </svg>
                <span>Cetak Dokumen Laporan (PDF)</span>
            </button>
        </div>
    </div>

    {{-- A4 Printable Sheet --}}
    <div class="max-w-4xl mx-auto bg-white border border-slate-300 shadow-xl rounded-none sm:rounded-lg p-8 sm:p-12 print:border-none print:shadow-none print:p-0">

        {{-- Official Kop Surat --}}
        <div class="border-b-4 border-double border-black pb-4 text-center mb-6">
            <div class="text-xl sm:text-2xl font-black uppercase tracking-wider text-black">
                P.A.D.I. SMART FARMING
            </div>
            <div class="text-sm sm:text-base font-bold uppercase tracking-wide text-emerald-800 mt-1">
                LAPORAN DATA PERTANIAN TERPADU &mdash; GOVERNMENT B2G
            </div>
            <div class="text-xs text-slate-600 mt-1">
                Platform Integrasi Data Pertanian, Sebaran Penyakit AI &amp; Produktivitas Padi Nasional
            </div>
        </div>

        {{-- Document Metadata --}}
        <div class="bg-slate-50 border border-slate-300 p-4 rounded text-xs mb-6 grid grid-cols-2 gap-y-2 print:bg-transparent print:border-slate-400">
            <div>
                <span class="text-slate-500 font-semibold block">Instansi Kedinasan:</span>
                <strong class="text-black text-sm">{{ $subscription->agency_name }}</strong>
            </div>
            <div>
                <span class="text-slate-500 font-semibold block">Penanggung Jawab (PIC):</span>
                <strong class="text-black text-sm">{{ $subscription->pic_name }} ({{ $subscription->pic_phone }})</strong>
            </div>
            <div>
                <span class="text-slate-500 font-semibold block">Periode Laporan:</span>
                <span class="text-slate-800 font-medium">
                    {{ $filters['start_date'] ? \Carbon\Carbon::parse($filters['start_date'])->isoFormat('D MMMM Y') : 'Semua Data Awal' }}
                    s/d
                    {{ $filters['end_date'] ? \Carbon\Carbon::parse($filters['end_date'])->isoFormat('D MMMM Y') : 'Saat Ini' }}
                </span>
            </div>
            <div>
                <span class="text-slate-500 font-semibold block">Waktu Generate Laporan:</span>
                <span class="text-slate-800 font-medium">{{ $generatedAt->isoFormat('D MMMM Y, HH:mm') }} WIB</span>
            </div>
        </div>

        {{-- Section: RINGKASAN --}}
        <div class="mb-8">
            <h2 class="text-xs font-bold uppercase tracking-wider text-black border-b border-black pb-1 mb-3">
                I. RINGKASAN DATA PERTANIAN
            </h2>
            <div class="grid grid-cols-4 gap-2 text-center text-xs">
                <div class="border border-slate-300 p-2.5 rounded bg-slate-50 print:bg-transparent">
                    <span class="text-[10px] text-slate-500 uppercase font-bold block">Total Petani</span>
                    <strong class="text-base text-black block mt-0.5">{{ number_format($overview['total_farmers']) }}</strong>
                </div>
                <div class="border border-slate-300 p-2.5 rounded bg-slate-50 print:bg-transparent">
                    <span class="text-[10px] text-slate-500 uppercase font-bold block">Total Lahan</span>
                    <strong class="text-base text-black block mt-0.5">{{ number_format($overview['total_farms']) }}</strong>
                </div>
                <div class="border border-slate-300 p-2.5 rounded bg-slate-50 print:bg-transparent">
                    <span class="text-[10px] text-slate-500 uppercase font-bold block">Aktivitas Tani</span>
                    <strong class="text-base text-black block mt-0.5">{{ number_format($overview['total_activities']) }}</strong>
                </div>
                <div class="border border-slate-300 p-2.5 rounded bg-slate-50 print:bg-transparent">
                    <span class="text-[10px] text-slate-500 uppercase font-bold block">Deteksi Penyakit</span>
                    <strong class="text-base text-black block mt-0.5">{{ number_format($overview['total_disease_detections']) }}</strong>
                </div>
            </div>
        </div>

        {{-- Section: DATA LAHAN --}}
        <div class="mb-8">
            <h2 class="text-xs font-bold uppercase tracking-wider text-black border-b border-black pb-1 mb-3">
                II. DATA LAHAN PERTANIAN &amp; PRODUKTIVITAS
            </h2>
            <table class="w-full text-left border-collapse border border-slate-400 text-xs">
                <thead>
                    <tr class="bg-slate-100 print:bg-slate-200">
                        <th class="border border-slate-400 p-1.5 font-bold">Wilayah / Nama Lahan</th>
                        <th class="border border-slate-400 p-1.5 font-bold">Komoditas</th>
                        <th class="border border-slate-400 p-1.5 font-bold text-right">Luas (Ha)</th>
                        <th class="border border-slate-400 p-1.5 font-bold text-right">Panen (Ton)</th>
                        <th class="border border-slate-400 p-1.5 font-bold text-right">Produktivitas</th>
                        <th class="border border-slate-400 p-1.5 font-bold text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($productivity as $p)
                        <tr>
                            <td class="border border-slate-300 p-1.5">
                                <strong>{{ $p['farm_name'] }}</strong>
                                <span class="text-[10px] text-slate-500 block">{{ $p['region'] }}</span>
                            </td>
                            <td class="border border-slate-300 p-1.5">{{ $p['commodity'] }}</td>
                            <td class="border border-slate-300 p-1.5 text-right">{{ number_format($p['area_ha'], 2) }}</td>
                            <td class="border border-slate-300 p-1.5 text-right">{{ number_format($p['total_harvest_ton'], 2) }}</td>
                            <td class="border border-slate-300 p-1.5 text-right font-semibold">{{ number_format($p['productivity_ton_per_ha'], 2) }} Ton/Ha</td>
                            <td class="border border-slate-300 p-1.5 text-center text-[10px] font-bold">
                                {{ $p['status_label'] }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="border border-slate-300 p-4 text-center text-slate-500">
                                Tidak ada data lahan pada filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Section: AKTIVITAS TANI --}}
        <div class="mb-8">
            <h2 class="text-xs font-bold uppercase tracking-wider text-black border-b border-black pb-1 mb-3">
                III. REKAPITULASI AKTIVITAS TANI
            </h2>
            <table class="w-full text-left border-collapse border border-slate-400 text-xs">
                <thead>
                    <tr class="bg-slate-100 print:bg-slate-200">
                        <th class="border border-slate-400 p-1.5 font-bold">Tanggal</th>
                        <th class="border border-slate-400 p-1.5 font-bold">Wilayah</th>
                        <th class="border border-slate-400 p-1.5 font-bold">Komoditas</th>
                        <th class="border border-slate-400 p-1.5 font-bold">Aktivitas</th>
                        <th class="border border-slate-400 p-1.5 font-bold">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activities as $a)
                        <tr>
                            <td class="border border-slate-300 p-1.5 whitespace-nowrap">{{ $a->occurred_at ? $a->occurred_at->format('d/m/Y') : '-' }}</td>
                            <td class="border border-slate-300 p-1.5">{{ $a->cropSeason?->farm?->regency?->name ?? 'Wilayah Terdaftar' }}</td>
                            <td class="border border-slate-300 p-1.5">{{ $a->cropSeason?->variety?->name ?? 'Padi' }}</td>
                            <td class="border border-slate-300 p-1.5 font-semibold">{{ ucfirst($a->type) }}</td>
                            <td class="border border-slate-300 p-1.5">{{ $a->notes ?: 'Tercatat' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="border border-slate-300 p-4 text-center text-slate-500">
                                Tidak ada data aktivitas budidaya.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Section: LAPORAN DETEKSI PENYAKIT --}}
        <div class="mb-8">
            <h2 class="text-xs font-bold uppercase tracking-wider text-black border-b border-black pb-1 mb-3">
                IV. LAPORAN DETEKSI PENYAKIT TANAMAN (AI DIAGNOSIS)
            </h2>
            <table class="w-full text-left border-collapse border border-slate-400 text-xs">
                <thead>
                    <tr class="bg-slate-100 print:bg-slate-200">
                        <th class="border border-slate-400 p-1.5 font-bold">Tanggal</th>
                        <th class="border border-slate-400 p-1.5 font-bold">Wilayah</th>
                        <th class="border border-slate-400 p-1.5 font-bold">Diagnosis Penyakit</th>
                        <th class="border border-slate-400 p-1.5 font-bold">Status</th>
                        <th class="border border-slate-400 p-1.5 font-bold">Rekomendasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($diseases as $d)
                        <tr>
                            <td class="border border-slate-300 p-1.5 whitespace-nowrap">{{ $d->scanned_at ? $d->scanned_at->format('d/m/Y') : '-' }}</td>
                            <td class="border border-slate-300 p-1.5">{{ $d->farm?->regency?->name ?? ($d->farm?->province?->name ?? 'Terdaftar') }}</td>
                            <td class="border border-slate-300 p-1.5 font-bold">{{ $d->predicted_class ?: 'Tidak teridentifikasi' }}</td>
                            <td class="border border-slate-300 p-1.5 uppercase text-[10px]">{{ $d->quality_status ?: 'Terdeteksi' }}</td>
                            <td class="border border-slate-300 p-1.5 text-[11px]">{{ $d->recommendation?->action ?: 'Penanganan standar PPL' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="border border-slate-300 p-4 text-center text-slate-500">
                                Tidak ada laporan penyakit yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Section: INSIGHT --}}
        <div class="mb-10">
            <h2 class="text-xs font-bold uppercase tracking-wider text-black border-b border-black pb-1 mb-3">
                V. INSIGHT &amp; REKOMENDASI KEDINASAN
            </h2>
            <div class="border border-slate-300 p-3 rounded text-xs space-y-2">
                <p>
                    <strong>Aktivitas Terbanyak:</strong>
                    {{ collect($insights['top_activities'] ?? [])->pluck('type')->implode(', ') ?: 'Data budidaya sedang berjalan.' }}
                </p>
                <p>
                    <strong>Penyakit Dominan:</strong>
                    {{ collect($insights['top_diseases'] ?? [])->pluck('disease')->implode(', ') ?: 'Kondisi tanaman aman terkendali.' }}
                </p>
                <p class="text-[11px] text-slate-600 italic">
                    * Catatan: Data ini diperbarui secara berkala berdasarkan aktivitas harian petani di platform P.A.D.I. Smart Farming.
                </p>
            </div>
        </div>

        {{-- Lembar Pengesahan / Formal Signature Footer --}}
        <div class="grid grid-cols-2 gap-8 text-center text-xs mt-12 pt-4 border-t border-slate-300 page-break-inside-avoid">
            <div>
                <p class="text-slate-500 mb-16">P.A.D.I. Smart Farming B2G Data Platform</p>
                <p class="font-bold border-t border-slate-400 pt-1 inline-block px-8">Sistem Verifikasi Otomatis</p>
            </div>
            <div>
                <p class="text-slate-500 mb-16">Perwakilan Resmi Instansi (PIC)</p>
                <p class="font-bold border-t border-slate-400 pt-1 inline-block px-8">{{ $subscription->pic_name }}</p>
            </div>
        </div>

        {{-- Formal Print Footer --}}
        <div class="mt-8 pt-3 border-t border-slate-300 text-[10px] text-slate-500 flex justify-between">
            <span>P.A.D.I. Smart Farming &bull; Government B2G Data Platform</span>
            <span>ID Langganan: #{{ $subscription->id }} &bull; Sifat: Rahasia Instansi</span>
        </div>

    </div>

</body>
</html>
