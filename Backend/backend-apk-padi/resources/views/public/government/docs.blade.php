<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/padi-logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0f172a] text-slate-200 font-['Plus_Jakarta_Sans',sans-serif] antialiased min-h-screen">

    {{-- Top Bar --}}
    <header class="border-b border-slate-800 bg-[#0f172a]/90 backdrop-blur sticky top-0 z-50 px-6 py-4">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('government.index') }}" class="flex items-center gap-2 text-white font-black text-lg">
                    🌾 P.A.D.I.
                </a>
                <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 font-bold border border-emerald-500/30">B2G API v1</span>
            </div>
            <div class="flex items-center gap-4 text-xs font-semibold">
                <a href="{{ route('government.index') }}" class="text-slate-400 hover:text-white">&larr; Portal B2G</a>
                <a href="{{ route('government.index') }}#form-langganan" class="px-4 py-2 rounded-lg bg-[#16A34A] text-white hover:bg-[#15803D]">Dapatkan Token</a>
            </div>
        </div>
    </header>

    <div class="max-w-6xl mx-auto px-6 py-12 grid grid-cols-1 md:grid-cols-4 gap-8">
        {{-- Sidebar Menu --}}
        <aside class="md:col-span-1 space-y-4 text-xs">
            <div class="font-bold uppercase tracking-wider text-slate-400 mb-2">Panduan Utama</div>
            <nav class="space-y-1">
                <a href="#overview" class="block px-3 py-2 rounded-lg bg-slate-800 text-emerald-400 font-bold">Ringkasan & Base URL</a>
                <a href="#auth" class="block px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800/60 font-semibold">Otentikasi Token 6 Digit</a>
                <a href="#rate-limit" class="block px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800/60 font-semibold">Rate Limiting & Keamanan</a>
            </nav>

            <div class="font-bold uppercase tracking-wider text-slate-400 pt-4 mb-2">Endpoints API</div>
            <nav class="space-y-1 font-mono text-[11px]">
                <a href="#ep-overview" class="block px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800/60">GET /overview</a>
                <a href="#ep-activities" class="block px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800/60">GET /activities</a>
                <a href="#ep-diseases" class="block px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800/60">GET /diseases</a>
                <a href="#ep-productivity" class="block px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800/60">GET /productivity</a>
                <a href="#ep-insights" class="block px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800/60">GET /insights</a>
            </nav>
        </aside>

        {{-- Main Content --}}
        <main class="md:col-span-3 space-y-12 text-sm text-slate-300">
            {{-- Section 1: Overview --}}
            <section id="overview" class="space-y-4">
                <h1 class="text-3xl font-black text-white tracking-tight">Dokumentasi API B2G P.A.D.I.</h1>
                <p class="text-slate-400 leading-relaxed">
                    API Business-to-Government (B2G) P.A.D.I. menyediakan akses data terpadu aktivitas budidaya, pemantauan serangan penyakit, kalkulasi produktivitas padi, dan indikator pertanian untuk sistem informasi dinas/pemerintah daerah.
                </p>

                <div class="p-4 rounded-xl bg-slate-900 border border-slate-800">
                    <span class="text-xs uppercase font-bold text-slate-400 tracking-wider">Base API URL:</span>
                    <div class="mt-1 font-mono font-bold text-emerald-400 text-sm break-all">
                        {{ $baseApiUrl }}
                    </div>
                </div>
            </section>

            {{-- Section 2: Authentication --}}
            <section id="auth" class="space-y-4 border-t border-slate-800 pt-8">
                <h2 class="text-2xl font-bold text-white">Otentikasi Token 6 Digit</h2>
                <p class="text-slate-400 leading-relaxed">
                    Semua endpoint API B2G dilindungi dengan otentikasi Bearer Token 6 digit angka yang diterbitkan setelah subscription Anda disetujui Administrator.
                </p>

                <div class="bg-slate-900 rounded-xl p-4 border border-slate-800 font-mono text-xs text-emerald-300">
                    <div>Authorization: Bearer 483921</div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800">
                        <span class="font-bold text-white block mb-1">Masa Berlaku Token</span>
                        <span class="text-slate-400">Token aktif selama 30 hari kalender sejak diterbitkan. Permintaan dengan token kedaluwarsa akan ditolak dengan status HTTP 403 Forbidden.</span>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800">
                        <span class="font-bold text-white block mb-1">Format Token</span>
                        <span class="text-slate-400">Tepat 6 digit angka acak aman (cryptographically secure random). Token terenkripsi SHA-256 pada basis data.</span>
                    </div>
                </div>
            </section>

            {{-- Section 3: Endpoints --}}
            <section class="space-y-10 border-t border-slate-800 pt-8">
                <h2 class="text-2xl font-bold text-white">Daftar Endpoint API</h2>

                {{-- Endpoint 1: Overview --}}
                <div id="ep-overview" class="space-y-3 bg-slate-900/80 rounded-2xl p-6 border border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 rounded bg-emerald-500/20 text-emerald-400 font-mono font-bold text-xs">GET</span>
                        <code class="font-mono font-bold text-white text-sm">/overview</code>
                    </div>
                    <p class="text-xs text-slate-400">Mengambil ringkasan makro data pertanian nasional/wilayah.</p>

                    <div class="bg-slate-950 rounded-xl p-4 font-mono text-xs overflow-x-auto border border-slate-800 text-slate-300">
<pre>{
  "success": true,
  "message": "Ringkasan data B2G berhasil diambil.",
  "data": {
    "total_farmers": 120,
    "total_farms": 145,
    "total_activities": 530,
    "total_disease_detections": 18,
    "productive_farms": 95,
    "need_attention_farms": 20,
    "insufficient_data_farms": 30
  }
}</pre>
                    </div>
                </div>

                {{-- Endpoint 2: Activities --}}
                <div id="ep-activities" class="space-y-3 bg-slate-900/80 rounded-2xl p-6 border border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 rounded bg-emerald-500/20 text-emerald-400 font-mono font-bold text-xs">GET</span>
                        <code class="font-mono font-bold text-white text-sm">/activities</code>
                    </div>
                    <p class="text-xs text-slate-400">Mengambil daftar riwayat aktivitas budidaya dengan paginasi dan filter database.</p>

                    <div class="text-xs text-slate-400">
                        <span class="font-bold text-slate-200">Query Parameters:</span>
                        <ul class="list-disc list-inside mt-1 space-y-1 font-mono text-[11px]">
                            <li><code class="text-emerald-400">start_date</code> (YYYY-MM-DD): Filter tanggal mulai aktivitas</li>
                            <li><code class="text-emerald-400">end_date</code> (YYYY-MM-DD): Filter tanggal akhir aktivitas</li>
                            <li><code class="text-emerald-400">region</code> (string): Filter nama kabupaten/kecamatan/desa</li>
                            <li><code class="text-emerald-400">commodity</code> (string): Filter varietas padi (e.g. Inpari 32)</li>
                            <li><code class="text-emerald-400">activity_type</code> (string): Filter jenis kegiatan (e.g. penanaman, pemupukan)</li>
                            <li><code class="text-emerald-400">per_page</code> (integer): Jumlah data per halaman (default 15)</li>
                        </ul>
                    </div>

                    <div class="bg-slate-950 rounded-xl p-4 font-mono text-xs overflow-x-auto border border-slate-800 text-slate-300">
<pre>{
  "success": true,
  "data": [
    {
      "id": 102,
      "activity_type": "Penanaman",
      "activity_date": "2026-09-01",
      "commodity": "Padi (Inpari 32)",
      "farm_name": "Lahan Sawah Blok B",
      "region": "Kec. Jatibarang, Kab. Indramayu",
      "status": "completed",
      "notes": "Pindah tanam bibit umur 21 HSS."
    }
  ],
  "pagination": { "current_page": 1, "total": 45, "per_page": 15 }
}</pre>
                    </div>
                </div>

                {{-- Endpoint 3: Diseases --}}
                <div id="ep-diseases" class="space-y-3 bg-slate-900/80 rounded-2xl p-6 border border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 rounded bg-emerald-500/20 text-emerald-400 font-mono font-bold text-xs">GET</span>
                        <code class="font-mono font-bold text-white text-sm">/diseases</code>
                    </div>
                    <p class="text-xs text-slate-400">Mengambil data pemantauan hama & penyakit padi dengan rekomendasi penanganan.</p>

                    <div class="bg-slate-950 rounded-xl p-4 font-mono text-xs overflow-x-auto border border-slate-800 text-slate-300">
<pre>{
  "success": true,
  "data": [
    {
      "id": 48,
      "disease": "Bacterial Leaf Blight (Hawar Daun Bakteri)",
      "quality_status": "diseased",
      "confidence": 0.945,
      "confidence_level": "Tinggi",
      "commodity": "Padi Sawah (Oryza sativa)",
      "farm_name": "Lahan Sawah Blok B",
      "region": "Kabupaten Indramayu",
      "detected_at": "2026-09-08T10:15:00.000000Z",
      "recommendation": "Lakukan penyemprotan bakterisida dan atur pengeringan lahan sementara."
    }
  ]
}</pre>
                    </div>
                </div>

                {{-- Endpoint 4: Productivity --}}
                <div id="ep-productivity" class="space-y-3 bg-slate-900/80 rounded-2xl p-6 border border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 rounded bg-emerald-500/20 text-emerald-400 font-mono font-bold text-xs">GET</span>
                        <code class="font-mono font-bold text-white text-sm">/productivity</code>
                    </div>
                    <p class="text-xs text-slate-400">Mengambil evaluasi hasil panen dan rasio produktivitas per hektar (ton/ha).</p>

                    <div class="bg-slate-950 rounded-xl p-4 font-mono text-xs overflow-x-auto border border-slate-800 text-slate-300">
<pre>{
  "success": true,
  "data": [
    {
      "farm_id": 12,
      "farm_name": "Lahan Sawah Subak A",
      "area_ha": 2.0,
      "commodity": "Padi (Ciherang)",
      "region": "Kabupaten Indramayu",
      "total_harvest_ton": 11.2,
      "productivity_ton_per_ha": 5.6,
      "status": "PRODUCTIVE",
      "status_label": "Produktif"
    }
  ]
}</pre>
                    </div>
                </div>

                {{-- Endpoint 5: Insights --}}
                <div id="ep-insights" class="space-y-3 bg-slate-900/80 rounded-2xl p-6 border border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 rounded bg-emerald-500/20 text-emerald-400 font-mono font-bold text-xs">GET</span>
                        <code class="font-mono font-bold text-white text-sm">/insights</code>
                    </div>
                    <p class="text-xs text-slate-400">Mengambil agregasi analitik, tren varietas dominan, dan rasio produktivitas wilayah.</p>
                </div>
            </section>
        </main>
    </div>

</body>
</html>
