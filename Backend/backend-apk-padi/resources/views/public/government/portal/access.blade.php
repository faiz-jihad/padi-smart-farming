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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/public/government-portal.css') }}?v={{ time() }}">
</head>
<body class="bg-slate-900 text-slate-100 font-['Plus_Jakarta_Sans',sans-serif] antialiased min-h-screen flex flex-col justify-between">

    {{-- Top Navigation Bar --}}
    <header class="border-b border-slate-800 bg-slate-900/90 backdrop-blur sticky top-0 z-50 px-6 py-4">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('government.index') }}" class="flex items-center gap-2 text-white font-black text-lg tracking-tight">
                    🌾 P.A.D.I.
                </a>
                <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 font-bold border border-emerald-500/30">
                    Government Portal B2G
                </span>
            </div>
            <div class="flex items-center gap-4 text-xs font-semibold">
                <a href="{{ route('government.index') }}" class="text-slate-400 hover:text-white transition">
                    &larr; Portal Publik
                </a>
                <a href="{{ route('government.docs') }}" target="_blank" class="text-slate-400 hover:text-white transition">
                    Dokumentasi API
                </a>
            </div>
        </div>
    </header>

    {{-- Main Container --}}
    <main class="max-w-xl mx-auto px-6 py-16 w-full flex-grow flex items-center justify-center">
        <div class="w-full bg-slate-800/80 border border-slate-700/80 rounded-2xl p-8 md:p-10 shadow-2xl shadow-emerald-950/20 backdrop-blur-xl">

            {{-- Badge & Heading --}}
            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-bold uppercase tracking-wider mb-4">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    Akses Khusus Instansi Pemerintah
                </div>
                <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight mb-2">
                    Government Data Portal
                </h1>
                <p class="text-slate-400 text-xs md:text-sm leading-relaxed">
                    Masukkan 6-digit token akses resmi kedinasan Anda untuk mengakses dashboard data pertanian, sebaran penyakit tanaman AI, dan mencetak laporan resmi.
                </p>
            </div>

            {{-- Flash Alert: Error --}}
            @if (session('error'))
                <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 text-xs md:text-sm flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 text-red-400 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <div>
                        <span class="font-bold block mb-0.5">Akses Ditolak</span>
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            {{-- Flash Alert: Status --}}
            @if (session('status'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs md:text-sm flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 text-emerald-400 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Access Form --}}
            <form action="{{ route('government.portal.access') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="token" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">
                        6-Digit Government Access Token
                    </label>
                    <div class="relative">
                        <input
                            type="text"
                            name="token"
                            id="token"
                            value="{{ old('token') }}"
                            maxlength="6"
                            inputmode="numeric"
                            autocomplete="off"
                            autofocus
                            placeholder="Contoh: 599979"
                            class="w-full px-4 py-3.5 bg-slate-900/90 border-2 border-slate-700 rounded-xl text-emerald-400 placeholder-slate-500 font-mono text-xl md:text-2xl font-bold tracking-[0.25em] text-center focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition"
                            required
                        >
                    </div>
                    <p class="text-[11px] text-slate-400 mt-2">
                        * Token hanya berupa 6 angka acak yang dikirimkan via WhatsApp ke nomor PIC instansi Anda setelah persetujuan Admin.
                    </p>
                </div>

                <button
                    type="submit"
                    class="w-full py-3.5 px-6 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm tracking-wide shadow-lg shadow-emerald-700/30 hover:shadow-emerald-600/40 transition duration-150 flex items-center justify-center gap-2 cursor-pointer"
                >
                    <span>Buka Government Dashboard</span>
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                    </svg>
                </button>
            </form>

            {{-- Security & Help Footer inside card --}}
            <div class="mt-8 pt-6 border-t border-slate-700/60 text-center">
                <p class="text-xs text-slate-400 leading-relaxed">
                    Belum memiliki token atau langganan kedinasan belum aktif?
                </p>
                <a href="{{ route('government.index') }}#form-langganan" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-400 hover:text-emerald-300 mt-1 transition">
                    <span>Daftarkan Instansi Anda di Portal B2G</span>
                    <span>&rarr;</span>
                </a>
            </div>

        </div>
    </main>

    {{-- Footer --}}
    <footer class="border-t border-slate-800/80 px-6 py-4 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} P.A.D.I. Smart Farming. Platform Integrasi Data Pemerintah (B2G).</p>
    </footer>

</body>
</html>
