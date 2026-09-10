<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - P.A.D.I. Smart Farming</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F8FAF8;
            color: #111827;
            margin: 0;
            padding: 0;
        }
        .nav-floating {
            max-width: 72rem;
            margin: 0 auto;
            border-radius: 9999px;
            padding: 0.85rem 1.75rem;
            background-color: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(229, 231, 235, 0.8);
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background-color: #16A34A;
            color: #ffffff;
            font-weight: 700;
            border-radius: 9999px;
            padding: 0.75rem 1.75rem;
            font-size: 0.875rem;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 8px 20px -4px rgba(22, 163, 74, 0.4);
            border: none;
            cursor: pointer;
        }
        .btn-primary:hover {
            background-color: #15803D;
            transform: translateY(-1px);
            box-shadow: 0 12px 24px -4px rgba(22, 163, 74, 0.45);
        }
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background-color: #ffffff;
            color: #374151;
            font-weight: 700;
            border-radius: 9999px;
            padding: 0.75rem 1.75rem;
            font-size: 0.875rem;
            text-decoration: none;
            border: 1px solid #E5E7EB;
            transition: all 0.2s ease;
        }
        .btn-secondary:hover {
            background-color: #F9FAFB;
            border-color: #D1D5DB;
            color: #111827;
            transform: translateY(-1px);
        }
        .stats-dark-container {
            background: linear-gradient(145deg, #071910 0%, #0c281a 100%);
            border: 1px solid rgba(16, 185, 129, 0.25);
            box-shadow: 0 25px 60px -15px rgba(7, 25, 16, 0.35);
            border-radius: 2rem;
            color: #ffffff;
            padding: 2.5rem;
        }
        .stat-card-glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1.25rem;
            padding: 1.5rem;
            transition: all 0.2s ease;
        }
        .stat-card-glass:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(52, 211, 153, 0.4);
        }
        .feature-card {
            background: #ffffff;
            border: 1px solid #E5E7EB;
            border-radius: 1.5rem;
            padding: 1.75rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            transition: all 0.25s ease;
            display: flex;
            flex-col: column;
            justify-content: space-between;
        }
        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.08);
            border-color: #A7F3D0;
        }
        .pricing-card {
            background: #ffffff;
            border: 2px solid #16A34A;
            border-radius: 2rem;
            padding: 2.5rem;
            box-shadow: 0 20px 50px -10px rgba(22, 163, 74, 0.15);
            position: relative;
            overflow: hidden;
        }
        .form-card {
            background: #ffffff;
            border: 1px solid #E5E7EB;
            border-radius: 2rem;
            padding: 2.5rem;
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.06);
        }
        .form-input {
            width: 100%;
            box-sizing: border-box;
            padding: 0.875rem 1.15rem;
            border-radius: 0.75rem;
            border: 1px solid #D1D5DB;
            font-size: 0.875rem;
            background-color: #ffffff;
            color: #111827;
            outline: none;
            transition: all 0.15s ease;
        }
        .form-input:focus {
            border-color: #16A34A;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.2);
        }
    </style>
</head>
<body class="selection:bg-emerald-100 selection:text-emerald-900">

    {{-- Top Floating Pill Navbar --}}
    <header class="sticky top-0 left-0 w-full z-50 px-4 sm:px-8 pt-4 pb-2 transition-all">
        <div class="nav-floating">
            <a href="{{ url('/') }}" class="flex items-center gap-3 text-decoration-none group">
                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-[#16A34A] shadow-xs">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M7 20h10" />
                        <path d="M10 20c5.5-2.5.8-6.4 3-10" />
                        <path d="M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z" />
                        <path d="M14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4 1-4.9 2z" />
                    </svg>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xl font-black tracking-tight text-gray-950">P.A.D.I.</span>
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#16A34A] animate-pulse"></span>
                        B2G Government
                    </span>
                </div>
            </a>

            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-gray-600">
                <a href="{{ url('/') }}" class="hover:text-[#16A34A] transition-colors text-decoration-none">Beranda</a>
                <a href="#fitur" class="hover:text-gray-950 transition-colors text-decoration-none">Data Tersedia</a>
                <a href="#paket" class="hover:text-gray-950 transition-colors text-decoration-none">Paket B2G</a>
                <a href="#form-langganan" class="hover:text-gray-950 transition-colors text-decoration-none">Pendaftaran</a>
                <a href="{{ route('government.docs') }}" class="inline-flex items-center gap-1.5 text-emerald-700 hover:text-emerald-900 font-bold transition-colors text-decoration-none">
                    <span>Dokumentasi API</span>
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                </a>
            </nav>

            <a href="#form-langganan" class="btn-primary text-xs sm:text-sm">
                <span>Daftar Langganan</span>
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
        </div>
    </header>

    {{-- Hero Section --}}
    <section class="max-w-6xl mx-auto px-4 sm:px-6 pt-14 pb-16 text-center">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-50 text-[#16A34A] border border-emerald-200 text-xs font-bold uppercase tracking-wider mb-6 shadow-xs">
            <svg class="w-4 h-4 text-[#16A34A]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 10h18M5 10v11M19 10v11M9 10v11M15 10v11M4 10l8-6 8 6"/></svg>
            <span>Saluran Resmi Business-to-Government (B2G) P.A.D.I.</span>
        </div>

        <h1 class="text-3xl sm:text-5xl md:text-6xl font-black text-gray-950 tracking-tight leading-tight max-w-4xl mx-auto mb-6">
            Akses Data Pertanian Terpadu, Radar Penyakit, & Produktivitas Nasional.
        </h1>

        <p class="text-base sm:text-lg text-gray-600 max-w-2xl mx-auto leading-relaxed mb-10">
            Platform integrasi data agrikultur resmi bagi Dinas Pertanian provinsi dan kabupaten untuk memantau siklus tanam, mitigasi dini hama wereng & blas, serta kalibrasi hasil panen secara teragregasi dan real-time.
        </p>

        <div class="flex flex-wrap items-center justify-center gap-4">
            <a href="#form-langganan" class="btn-primary text-sm sm:text-base py-3.5 px-8">
                <span>Daftarkan Instansi Anda</span>
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
            <a href="{{ route('government.docs') }}" class="btn-secondary text-sm sm:text-base py-3.5 px-8">
                <span>Pelajari Dokumentasi API</span>
                <svg class="w-4 h-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </a>
        </div>
    </section>

    {{-- Live Overview Stats Section --}}
    <section class="max-w-6xl mx-auto px-4 sm:px-6 mb-20">
        <div class="stats-dark-container">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8 border-b border-emerald-900/60 pb-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-bold uppercase tracking-wider mb-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        <span>Live Telemetry & Aggregation</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-black text-white m-0">Cakupan Data Pertanian Nasional</h2>
                </div>
                <div class="text-xs font-semibold text-emerald-300 bg-emerald-950/80 px-4 py-2 rounded-xl border border-emerald-800/40">
                    Diperbarui Otomatis via IoT & Aplikasi Petani P.A.D.I.
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6">
                <div class="stat-card-glass text-center sm:text-left">
                    <div class="text-3xl sm:text-5xl font-black text-emerald-400 tracking-tight mb-1">{{ number_format($overview['total_farmers']) }}</div>
                    <div class="text-xs sm:text-sm font-bold text-slate-200">Petani Terdaftar</div>
                    <div class="text-[11px] text-slate-400 mt-1">Pengguna aktif terverifikasi</div>
                </div>

                <div class="stat-card-glass text-center sm:text-left">
                    <div class="text-3xl sm:text-5xl font-black text-emerald-400 tracking-tight mb-1">{{ number_format($overview['total_farms']) }}</div>
                    <div class="text-xs sm:text-sm font-bold text-slate-200">Lahan Pertanian</div>
                    <div class="text-[11px] text-slate-400 mt-1">Poligon petak sawah aktif</div>
                </div>

                <div class="stat-card-glass text-center sm:text-left">
                    <div class="text-3xl sm:text-5xl font-black text-emerald-400 tracking-tight mb-1">{{ number_format($overview['total_activities']) }}</div>
                    <div class="text-xs sm:text-sm font-bold text-slate-200">Aktivitas Budidaya</div>
                    <div class="text-[11px] text-slate-400 mt-1">Siklus tanam & pemupukan</div>
                </div>

                <div class="stat-card-glass text-center sm:text-left">
                    <div class="text-3xl sm:text-5xl font-black text-emerald-400 tracking-tight mb-1">{{ $insights['productive_rate_pct'] }}%</div>
                    <div class="text-xs sm:text-sm font-bold text-slate-200">Rasio Produktif</div>
                    <div class="text-[11px] text-slate-400 mt-1">Hasil panen &ge; 4.5 Ton / Ha</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Data Modules / Features --}}
    <section id="fitur" class="max-w-6xl mx-auto px-4 sm:px-6 mb-24">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-50 text-[#16A34A] border border-emerald-200 text-xs font-bold uppercase tracking-wider mb-3">
                <span>Modul Data B2G</span>
            </div>
            <h2 class="text-3xl sm:text-4xl font-black text-gray-950 tracking-tight m-0">Katalog Data untuk Pembuat Kebijakan</h2>
            <p class="text-gray-600 text-sm sm:text-base mt-2">Seluruh output disajikan dalam format JSON REST API standar dengan autentikasi Token 6 Digit dan pagination siap pakai.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Card 1 --}}
            <div class="feature-card">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-[#16A34A] flex items-center justify-center font-bold mb-5 shadow-xs">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/></svg>
                    </div>
                    <div class="text-xs font-mono font-bold text-emerald-700 uppercase tracking-wider mb-1">/activities</div>
                    <h3 class="font-extrabold text-gray-950 text-lg mb-2">Aktivitas Budidaya</h3>
                    <p class="text-xs text-gray-600 leading-relaxed mb-4">
                        Log penanaman, jadwal irigasi, pemupukan organik/anorganik, dan estimasi tanggal panen per wilayah.
                    </p>
                </div>
                <div class="pt-4 border-t border-gray-100 flex items-center justify-between text-xs font-bold text-[#16A34A]">
                    <span>Filter: Daerah, Tanggal</span>
                    <span>JSON &rarr;</span>
                </div>
            </div>

            {{-- Card 2 --}}
            <div class="feature-card">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold mb-5 shadow-xs">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    </div>
                    <div class="text-xs font-mono font-bold text-rose-700 uppercase tracking-wider mb-1">/diseases</div>
                    <h3 class="font-extrabold text-gray-950 text-lg mb-2">Radar Penyakit & Hama</h3>
                    <p class="text-xs text-gray-600 leading-relaxed mb-4">
                        Deteksi AI citra daun padi (blas, hawar daun, tungro), tingkat keparahan, dan status validasi PPL lapangan.
                    </p>
                </div>
                <div class="pt-4 border-t border-gray-100 flex items-center justify-between text-xs font-bold text-rose-600">
                    <span>Mitigasi Wabah Pangan</span>
                    <span>JSON &rarr;</span>
                </div>
            </div>

            {{-- Card 3 --}}
            <div class="feature-card">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold mb-5 shadow-xs">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    </div>
                    <div class="text-xs font-mono font-bold text-indigo-700 uppercase tracking-wider mb-1">/productivity</div>
                    <h3 class="font-extrabold text-gray-950 text-lg mb-2">Analitik Produktivitas</h3>
                    <p class="text-xs text-gray-600 leading-relaxed mb-4">
                        Kalkulasi panen gabah per hektar (Ton/Ha) dan klasifikasi lahan produktif vs lahan butuh atensi dinas.
                    </p>
                </div>
                <div class="pt-4 border-t border-gray-100 flex items-center justify-between text-xs font-bold text-indigo-600">
                    <span>Evaluasi Swasembada</span>
                    <span>JSON &rarr;</span>
                </div>
            </div>

            {{-- Card 4 --}}
            <div class="feature-card">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold mb-5 shadow-xs">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </div>
                    <div class="text-xs font-mono font-bold text-amber-700 uppercase tracking-wider mb-1">/insights</div>
                    <h3 class="font-extrabold text-gray-950 text-lg mb-2">Executive Insights</h3>
                    <p class="text-xs text-gray-600 leading-relaxed mb-4">
                        Ringkasan indikator makro, varietas unggul dominan (Ciherang, Inpari, IR64), dan rasio sebaran geografis.
                    </p>
                </div>
                <div class="pt-4 border-t border-gray-100 flex items-center justify-between text-xs font-bold text-amber-600">
                    <span>Dashboard Pimpinan</span>
                    <span>JSON &rarr;</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Subscription Package & Pricing --}}
    <section id="paket" class="max-w-6xl mx-auto px-4 sm:px-6 mb-24">
        <div class="max-w-xl mx-auto pricing-card text-center">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 text-xs font-bold uppercase tracking-wider mb-2 border border-emerald-200">
                <span>Paket Resmi B2G</span>
            </div>

            <h3 class="text-2xl sm:text-3xl font-black text-gray-950 mt-1 mb-2">Langganan Akses API Pemerintah</h3>
            <p class="text-gray-500 text-xs sm:text-sm max-w-md mx-auto mb-6">Mendukung integrasi sistem Satu Data Indonesia, Command Center, dan dashboard dinas pertanian daerah.</p>

            <div class="flex items-baseline justify-center gap-2 mb-8 bg-[#F8FAF8] py-5 px-6 rounded-2xl border border-gray-200/90 max-w-sm mx-auto">
                <span class="text-3xl sm:text-3xl font-black text-gray-900 tracking-tight">
    Rp {{ number_format($planPrice, 0, ',', '.') }}
</span>
                <span class="text-xs sm:text-sm font-bold text-gray-500">/ {{ $planDays }} Hari</span>
            </div>

            <ul class="text-left space-y-3.5 mb-8 text-xs sm:text-sm text-gray-700 max-w-md mx-auto list-none p-0">
                <li class="flex items-start gap-3">
                    <div class="w-5 h-5 rounded-full bg-emerald-100 text-[#16A34A] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">✓</div>
                    <span><strong>5 Endpoint REST API Utama:</strong> /overview, /activities, /diseases, /productivity, /insights</span>
                </li>
                <li class="flex items-start gap-3">
                    <div class="w-5 h-5 rounded-full bg-emerald-100 text-[#16A34A] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">✓</div>
                    <span><strong>Token Otentikasi 6 Digit:</strong> Diterbitkan khusus dengan enkripsi kriptografi SHA-256</span>
                </li>
                <li class="flex items-start gap-3">
                    <div class="w-5 h-5 rounded-full bg-emerald-100 text-[#16A34A] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">✓</div>
                    <span><strong>Filter Granular:</strong> Parameter wilayah, tanggal, dan komoditas</span>
                </li>
                <li class="flex items-start gap-3">
                    <div class="w-5 h-5 rounded-full bg-emerald-100 text-[#16A34A] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">✓</div>
                    <span><strong>Konfirmasi Billing:</strong> Komunikasi langsung via WhatsApp Resmi Administrator P.A.D.I.</span>
                </li>
                <li class="flex items-start gap-3">
                    <div class="w-5 h-5 rounded-full bg-emerald-100 text-[#16A34A] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">✓</div>
                    <span><strong>Perlindungan Privasi:</strong> Data agregat terlindungi sesuai standar GovTech</span>
                </li>
            </ul>

            <a href="#form-langganan" class="btn-primary w-full py-4 text-base">
                <span>Pilih Paket & Isi Formulir</span>
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>
            </a>
        </div>
    </section>

    {{-- Subscription Form Section --}}
    <section id="form-langganan" class="max-w-3xl mx-auto px-4 sm:px-6 mb-28">
        <div class="form-card">
            <div class="text-center mb-10">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-[#16A34A] flex items-center justify-center font-bold mx-auto mb-3">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                </div>
                <h2 class="text-2xl sm:text-3xl font-black text-gray-950 m-0">Formulir Pendaftaran Instansi</h2>
                <p class="text-sm text-gray-600 mt-2">Lengkapi identitas kedinasan di bawah ini. Setelah mendaftar, hubungi Administrator P.A.D.I. via WhatsApp untuk instruksi pembayaran dan penerbitan Token 6 Digit Anda.</p>
            </div>

            @if ($errors->any())
                <div style="background-color: #FEF2F2; border: 1px solid #FECACA; border-radius: 1rem; padding: 1rem; margin-bottom: 1.5rem; color: #991B1B;">
                    <div style="font-weight: 700; font-size: 0.875rem; margin-bottom: 0.25rem;">Harap periksa isian berikut:</div>
                    <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.75rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('government.subscribe') }}">
                @csrf

                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #374151; margin-bottom: 0.5rem;">Nama Instansi / Dinas Pertanian *</label>
                    <input type="text" name="agency_name" value="{{ old('agency_name') }}" required placeholder="Contoh: Dinas Pertanian dan Ketahanan Pangan Kab. Indramayu" class="form-input">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" style="margin-bottom: 1.25rem;">
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #374151; margin-bottom: 0.5rem;">Email Resmi Instansi *</label>
                        <input type="email" name="agency_email" value="{{ old('agency_email') }}" required placeholder="dinas.pertanian@indramayukab.go.id" class="form-input">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #374151; margin-bottom: 0.5rem;">Nama PIC Penanggung Jawab *</label>
                        <input type="text" name="pic_name" value="{{ old('pic_name') }}" required placeholder="Ir. H. Budi Santoso, M.Si" class="form-input">
                    </div>
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #374151; margin-bottom: 0.5rem;">Nomor Telepon / WhatsApp PIC *</label>
                    <input type="text" name="pic_phone" value="{{ old('pic_phone') }}" required placeholder="081234567890" class="form-input">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #374151; margin-bottom: 0.5rem;">Keperluan & Tujuan Akses Data</label>
                    <textarea name="purpose" rows="3" placeholder="Contoh: Integrasi dashboard ketahanan pangan daerah, pemetaan sebaran penyakit tanaman, dan mitigasi gagal panen..." class="form-input">{{ old('purpose') }}</textarea>
                </div>

                {{-- Order Summary Box --}}
                <div style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 1rem; padding: 1.25rem; margin-bottom: 1.75rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <div style="font-weight: 800; font-size: 0.875rem; color: #0F172A;">{{ $planName }} ({{ $planDays }} Hari Kalender)</div>
                        <div style="font-size: 0.75rem; color: #64748B; margin-top: 0.25rem;">Akses penuh 5 endpoint REST API & token otentikasi 6 digit</div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 0.75rem; color: #64748B;">Total Tagihan:</div>
                        <div style="font-weight: 900; font-size: 1.5rem; color: #15803D; letter-spacing: -0.02em;">
                            Rp {{ number_format($planPrice, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-primary w-full py-4 text-base" style="border-radius: 0.85rem;">
                    <span>Daftar &amp; Lanjutkan ke Billing WhatsApp &rarr;</span>
                </button>
            </form>
        </div>
    </section>

    {{-- Clean Footer --}}
    <footer style="background-color: #030712; color: #9CA3AF; padding: 3rem 1.5rem; border-top: 1px solid #111827; font-size: 0.875rem;">
        <div style="max-w: 72rem; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; font-weight: 700; color: #ffffff;">
                <div style="width: 2rem; height: 2rem; border-radius: 9999px; background-color: #16A34A; display: flex; align-items: center; justify-content: center; font-size: 0.75rem;">🌾</div>
                <span>P.A.D.I. Smart Farming &bull; Government B2G</span>
            </div>
            <div style="display: flex; align-items: center; gap: 1.5rem; font-size: 0.75rem; font-weight: 600;">
                <a href="{{ url('/') }}" style="color: #9CA3AF; text-decoration: none;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#9CA3AF'">Beranda</a>
                <a href="{{ route('government.docs') }}" style="color: #9CA3AF; text-decoration: none;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#9CA3AF'">Dokumentasi API</a>
                <a href="{{ url('/admin/login') }}" style="color: #9CA3AF; text-decoration: none;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#9CA3AF'">Admin Console</a>
            </div>
        </div>
    </footer>

</body>
</html>
