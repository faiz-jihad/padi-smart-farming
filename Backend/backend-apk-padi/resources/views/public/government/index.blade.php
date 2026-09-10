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
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: {
                        leaf: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#16a34a',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                            950: '#022c22'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --green-950: #022c22;
            --green-900: #064e3b;
            --green-800: #065f46;
            --green-700: #047857;
            --green-500: #16a34a;
            --green-50: #f0fdf4;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #dbeee2;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Plus Jakarta Sans', sans-serif; color: var(--ink); background: #fbfefc; }
        a { text-decoration: none; }
        .shell { width: min(1160px, calc(100% - 32px)); margin: 0 auto; }
        .nav { position: sticky; top: 0; z-index: 50; padding: 14px 0; background: rgba(251, 254, 252, .78); backdrop-filter: blur(18px); border-bottom: 1px solid rgba(219, 238, 226, .8); }
        .nav-inner { min-height: 64px; border: 1px solid rgba(219, 238, 226, .95); border-radius: 999px; background: rgba(255, 255, 255, .92); box-shadow: 0 18px 45px rgba(2, 44, 34, .08); display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 10px 12px 10px 18px; }
        .brand { display: flex; align-items: center; gap: 12px; color: var(--ink); }
        .brand-mark { width: 42px; height: 42px; border-radius: 16px; background: var(--green-950); color: white; display: grid; place-items: center; font-weight: 900; }
        .nav-links { display: flex; align-items: center; gap: 22px; font-size: 13px; font-weight: 800; }
        .nav-links a { color: #475569; }
        .nav-links a:hover { color: var(--green-800); }
        .btn { border: 0; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 9px; border-radius: 999px; font-weight: 900; transition: transform .18s ease, box-shadow .18s ease, background .18s ease; white-space: nowrap; }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary { background: var(--green-700); color: white; padding: 13px 20px; box-shadow: 0 16px 30px rgba(4, 120, 87, .22); }
        .btn-primary:hover { background: var(--green-800); }
        .btn-white { background: white; color: var(--green-900); padding: 13px 20px; border: 1px solid rgba(255,255,255,.68); }
        .hero { min-height: 720px; margin-top: -94px; padding: 126px 0 70px; position: relative; color: white; background-image: linear-gradient(90deg, rgba(2, 44, 34, .96) 0%, rgba(2, 44, 34, .86) 42%, rgba(2, 44, 34, .36) 100%), url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1800&q=80'); background-size: cover; background-position: center; }
        .hero-grid { display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(360px, .95fr); gap: 44px; align-items: center; }
        .eyebrow { display: inline-flex; align-items: center; gap: 9px; padding: 8px 12px; border-radius: 999px; background: rgba(255,255,255,.12); color: #bbf7d0; border: 1px solid rgba(187, 247, 208, .25); font-size: 12px; font-weight: 900; letter-spacing: .08em; text-transform: uppercase; }
        .hero h1 { margin: 22px 0 18px; max-width: 760px; font-size: clamp(42px, 6vw, 76px); line-height: .98; letter-spacing: -.055em; font-weight: 900; }
        .hero-copy { max-width: 650px; color: rgba(255,255,255,.78); font-size: 18px; line-height: 1.75; margin: 0 0 30px; }
        .hero-actions { display: flex; flex-wrap: wrap; gap: 12px; }
        .trust-row { margin-top: 34px; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; max-width: 650px; }
        .trust-card { border: 1px solid rgba(255,255,255,.16); background: rgba(255,255,255,.08); border-radius: 18px; padding: 16px; }
        .trust-card strong { display: block; font-size: 24px; line-height: 1; }
        .trust-card span { color: rgba(255,255,255,.68); font-size: 12px; font-weight: 700; }
        .data-panel { border: 1px solid rgba(255,255,255,.22); background: rgba(255,255,255,.13); backdrop-filter: blur(20px); border-radius: 30px; padding: 18px; box-shadow: 0 30px 80px rgba(0,0,0,.25); }
        .dashboard { border-radius: 24px; background: #f8fff9; color: var(--ink); overflow: hidden; box-shadow: inset 0 0 0 1px rgba(6, 95, 70, .08); }
        .dash-top { display: flex; justify-content: space-between; align-items: center; padding: 16px 18px; border-bottom: 1px solid #e5f4e8; }
        .dot-row { display: flex; gap: 6px; }
        .dot-row span { width: 9px; height: 9px; border-radius: 999px; background: #86efac; }
        .dash-body { padding: 18px; display: grid; gap: 14px; }
        .metric-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .metric { background: white; border: 1px solid #dff3e5; border-radius: 18px; padding: 16px; }
        .metric label { display: block; color: var(--muted); font-size: 11px; font-weight: 900; text-transform: uppercase; }
        .metric strong { display: block; color: var(--green-900); font-size: 30px; margin-top: 5px; letter-spacing: -.04em; }
        .map-card { min-height: 190px; border-radius: 20px; background: linear-gradient(90deg, rgba(4, 120, 87, .12) 1px, transparent 1px), linear-gradient(rgba(4, 120, 87, .12) 1px, transparent 1px), #ecfdf5; background-size: 28px 28px; border: 1px solid #c7ead2; position: relative; overflow: hidden; }
        .map-card:before { content: ''; position: absolute; width: 210px; height: 130px; left: 48px; top: 36px; border-radius: 54% 46% 62% 38%; background: rgba(4, 120, 87, .72); box-shadow: 120px 20px 0 rgba(22, 163, 74, .48), 58px 88px 0 rgba(101, 163, 13, .42); }
        .map-card:after { content: 'Early warning: wereng meningkat di 3 kecamatan'; position: absolute; left: 16px; right: 16px; bottom: 16px; padding: 11px 13px; border-radius: 14px; background: rgba(255,255,255,.92); color: var(--green-900); font-size: 12px; font-weight: 900; box-shadow: 0 12px 30px rgba(6, 95, 70, .14); }
        .section { padding: 82px 0; }
        .section-head { max-width: 720px; margin: 0 auto 34px; text-align: center; }
        .section-head h2 { margin: 0; font-size: clamp(30px, 4vw, 48px); line-height: 1.06; letter-spacing: -.045em; font-weight: 900; }
        .section-head p { margin: 14px auto 0; color: var(--muted); line-height: 1.75; }
        .mini-label { color: var(--green-700); text-transform: uppercase; letter-spacing: .12em; font-size: 12px; font-weight: 900; }
        .modules { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; }
        .module { background: white; border: 1px solid var(--line); border-radius: 24px; padding: 22px; box-shadow: 0 18px 50px rgba(15, 23, 42, .05); }
        .module-icon { width: 48px; height: 48px; border-radius: 16px; display: grid; place-items: center; background: var(--green-50); color: var(--green-700); font-weight: 900; margin-bottom: 18px; }
        .module code { color: var(--green-700); font-weight: 900; font-size: 12px; }
        .module h3 { margin: 7px 0 9px; font-weight: 900; font-size: 18px; }
        .module p { color: var(--muted); font-size: 13px; line-height: 1.65; margin: 0; }
        .workflow { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: stretch; }
        .workflow-card, .price-card, .form-card { background: white; border: 1px solid var(--line); border-radius: 30px; padding: 30px; box-shadow: 0 22px 70px rgba(2, 44, 34, .07); }
        .steps { display: grid; gap: 14px; margin-top: 20px; }
        .step { display: grid; grid-template-columns: 44px 1fr; gap: 14px; align-items: start; }
        .step-num { width: 44px; height: 44px; border-radius: 16px; background: var(--green-900); color: white; display: grid; place-items: center; font-weight: 900; }
        .step strong { display: block; font-weight: 900; margin-bottom: 4px; }
        .step span { color: var(--muted); font-size: 13px; line-height: 1.55; }
        .price-card { background: var(--green-950); color: white; border-color: rgba(187,247,208,.18); }
        .price { margin: 18px 0; padding: 20px; border-radius: 22px; background: rgba(255,255,255,.08); }
        .price strong { font-size: clamp(34px, 4vw, 48px); line-height: 1; letter-spacing: -.05em; }
        .plan-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin-top: 18px; }
        .plan-card { border: 1px solid rgba(187,247,208,.22); background: rgba(255,255,255,.07); border-radius: 22px; padding: 18px; display: grid; gap: 12px; }
        .plan-card.is-featured { background: white; color: var(--green-950); border-color: #86efac; box-shadow: 0 18px 44px rgba(2,44,34,.18); }
        .plan-card h3 { margin: 0; font-size: 20px; font-weight: 900; letter-spacing: -.03em; }
        .plan-card p { margin: 0; color: inherit; opacity: .74; font-size: 13px; line-height: 1.6; }
        .plan-price { font-size: 26px; font-weight: 900; letter-spacing: -.04em; }
        .plan-note { padding: 10px 12px; border-radius: 14px; background: rgba(255,255,255,.11); font-size: 12px; font-weight: 800; }
        .plan-card.is-featured .plan-note { background: var(--green-50); color: var(--green-800); }
        .check-list { display: grid; gap: 12px; padding: 0; margin: 22px 0 0; list-style: none; }
        .check-list li { display: grid; grid-template-columns: 24px 1fr; gap: 10px; color: rgba(255,255,255,.78); font-size: 13px; line-height: 1.55; }
        .check-list li:before { content: '\\2713'; color: #86efac; font-weight: 900; }
        .plan-card.is-featured .check-list li { color: #475569; }
        .plan-card.is-featured .check-list li:before { color: var(--green-700); }
        .access-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin-top: 20px; }
        .access-card { border: 1px solid var(--line); background: #fff; border-radius: 20px; padding: 18px; }
        .access-card strong { display: block; color: var(--green-900); font-size: 14px; margin-bottom: 6px; }
        .access-card span { display: block; color: var(--muted); font-size: 12px; line-height: 1.6; }
        .form-wrap { display: grid; grid-template-columns: .86fr 1.14fr; gap: 22px; align-items: start; }
        .form-card { padding: 26px; }
        .plan-choice-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
        .plan-choice { position: relative; display: block; cursor: pointer; }
        .plan-choice input { position: absolute; opacity: 0; pointer-events: none; }
        .plan-choice-box { min-height: 142px; border: 1px solid #dbe5df; border-radius: 18px; padding: 16px; background: #fbfefc; display: grid; align-content: start; gap: 8px; transition: border .18s ease, box-shadow .18s ease, background .18s ease; }
        .plan-choice input:checked + .plan-choice-box { border-color: var(--green-500); box-shadow: 0 0 0 4px rgba(22, 163, 74, .12); background: var(--green-50); }
        .plan-choice-title { font-weight: 900; color: var(--green-950); }
        .plan-choice-price { font-size: 20px; font-weight: 900; color: var(--green-800); letter-spacing: -.03em; }
        .plan-choice-caption { color: var(--muted); font-size: 12px; line-height: 1.55; }
        .input-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .field { display: grid; gap: 7px; margin-bottom: 14px; }
        .field label { font-size: 12px; font-weight: 900; color: #334155; }
        .field input, .field textarea { width: 100%; border: 1px solid #dbe5df; border-radius: 16px; padding: 14px 15px; outline: none; color: var(--ink); background: #fbfefc; font: inherit; font-size: 14px; }
        .field input:focus, .field textarea:focus { border-color: var(--green-500); box-shadow: 0 0 0 4px rgba(22, 163, 74, .12); background: white; }
        .summary { border: 1px solid var(--line); background: var(--green-50); border-radius: 20px; padding: 18px; display: flex; justify-content: space-between; gap: 16px; margin: 18px 0; }
        footer { background: #020617; color: #94a3b8; padding: 40px 0; }
        .error-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 18px; padding: 15px; color: #991b1b; margin-bottom: 18px; font-size: 13px; }
        @media (max-width: 980px) {
            .nav-links { display: none; }
            .hero { min-height: auto; padding-top: 118px; }
            .hero-grid, .workflow, .form-wrap { grid-template-columns: 1fr; }
            .modules { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 640px) {
            .shell { width: min(100% - 24px, 1160px); }
            .nav-inner { border-radius: 24px; align-items: flex-start; }
            .hero { margin-top: -104px; padding-top: 128px; }
            .hero h1 { font-size: 42px; }
            .hero-copy { font-size: 15px; }
            .trust-row, .modules, .metric-grid, .input-grid, .plan-grid, .access-grid, .plan-choice-grid { grid-template-columns: 1fr; }
            .section { padding: 58px 0; }
            .workflow-card, .price-card, .form-card { padding: 22px; border-radius: 24px; }
            .summary { flex-direction: column; }
        }
    </style>
</head>
<body>
    <header class="nav">
        <div class="shell nav-inner">
            <a href="{{ url('/') }}" class="brand" aria-label="P.A.D.I. Smart Farming">
                <div class="brand-mark">P</div>
                <div>
                    <div style="font-size:19px;font-weight:900;letter-spacing:-0.04em;">P.A.D.I.</div>
                    <div style="font-size:11px;font-weight:900;color:var(--green-700);text-transform:uppercase;letter-spacing:.12em;">Government Data Access</div>
                </div>
            </a>
            <nav class="nav-links" aria-label="Navigasi Government">
                <a href="#data">Data</a>
                <a href="#alur">Alur Akses</a>
                <a href="#paket">Paket</a>
                <a href="{{ route('government.docs') }}">Dokumentasi API</a>
            </nav>
            <a href="#form-langganan" class="btn btn-primary">Daftarkan Instansi</a>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="shell hero-grid">
                <div>
                    <div class="eyebrow"><span style="width:8px;height:8px;border-radius:999px;background:#86efac;display:inline-block;"></span>Portal resmi B2G P.A.D.I.</div>
                    <h1>Akses Data Pertanian Terpadu untuk keputusan pemerintah yang lebih cepat.</h1>
                    <p class="hero-copy">Satu portal untuk memantau sebaran lahan, aktivitas budidaya, deteksi penyakit padi, produktivitas panen, dan early warning wilayah. Dibuat untuk dinas, command center, dan pengambil kebijakan pangan.</p>
                    <div class="hero-actions">
                        <a href="#form-langganan" class="btn btn-primary">Daftarkan Instansi Anda</a>
                        <a href="{{ route('government.docs') }}" class="btn btn-white">Lihat Dokumentasi API</a>
                    </div>
                    <div class="trust-row">
                        <div class="trust-card"><strong>{{ number_format($overview['total_farmers']) }}</strong><span>Petani terdata</span></div>
                        <div class="trust-card"><strong>{{ number_format($overview['total_farms']) }}</strong><span>Lahan aktif</span></div>
                        <div class="trust-card"><strong>{{ $insights['productive_rate_pct'] }}%</strong><span>Rasio produktif</span></div>
                    </div>
                </div>

                <div class="data-panel" aria-label="Pratinjau dashboard P.A.D.I.">
                    <div class="dashboard">
                        <div class="dash-top">
                            <div><div style="font-weight:900;color:var(--green-950);">P.A.D.I. Data Center</div><div style="font-size:12px;color:var(--muted);font-weight:700;">Live aggregation wilayah</div></div>
                            <div class="dot-row"><span></span><span></span><span></span></div>
                        </div>
                        <div class="dash-body">
                            <div class="metric-grid">
                                <div class="metric"><label>Aktivitas budidaya</label><strong>{{ number_format($overview['total_activities']) }}</strong></div>
                                <div class="metric"><label>Kasus penyakit</label><strong>{{ number_format($overview['total_disease_detections'] ?? 0) }}</strong></div>
                            </div>
                            <div class="map-card"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="data" class="section">
            <div class="shell">
                <div class="section-head">
                    <div class="mini-label">Modul data siap integrasi</div>
                    <h2>API yang langsung berguna untuk monitoring pangan daerah.</h2>
                    <p>Setiap endpoint dirancang agar mudah masuk ke dashboard dinas, command center, atau sistem pelaporan internal pemerintah.</p>
                </div>
                <div class="modules">
                    <article class="module"><div class="module-icon">01</div><code>/overview</code><h3>Ringkasan Wilayah</h3><p>Total petani, lahan, luas tanam, aktivitas, penyakit, dan indikator produktivitas.</p></article>
                    <article class="module"><div class="module-icon">02</div><code>/activities</code><h3>Kegiatan Lahan</h3><p>Riwayat tanam, pemupukan, pengairan, semprot, biaya, dan catatan budidaya.</p></article>
                    <article class="module"><div class="module-icon">03</div><code>/diseases</code><h3>Radar Penyakit</h3><p>Deteksi AI, confidence, validasi PPL, lokasi kasus, dan status penanganan.</p></article>
                    <article class="module"><div class="module-icon">04</div><code>/productivity</code><h3>Produktivitas Panen</h3><p>Estimasi hasil panen, tonase per hektar, varietas dominan, dan lahan prioritas.</p></article>
                </div>
            </div>
        </section>

        <section id="alur" class="section" style="background:#f3fbf5;border-top:1px solid var(--line);border-bottom:1px solid var(--line);">
            <div class="shell workflow">
                <div class="workflow-card">
                    <div class="mini-label">Alur resmi</div>
                    <h2 style="font-size:38px;line-height:1.08;letter-spacing:-.045em;font-weight:900;margin:10px 0 0;">Dari pendaftaran sampai token API aktif.</h2>
                    <div class="steps">
                        <div class="step"><div class="step-num">1</div><div><strong>Daftarkan instansi</strong><span>Isi identitas dinas, PIC, email resmi, dan tujuan akses data.</span></div></div>
                        <div class="step"><div class="step-num">2</div><div><strong>Konfirmasi billing</strong><span>Admin P.A.D.I. memberi instruksi pembayaran melalui WhatsApp resmi.</span></div></div>
                        <div class="step"><div class="step-num">3</div><div><strong>Aktivasi token</strong><span>Setelah diverifikasi, token 6 digit diterbitkan untuk akses API.</span></div></div>
                    </div>
                </div>

                <div id="paket" class="price-card">
                    <div class="mini-label" style="color:#86efac;">Paket B2G</div>
                    <h2 style="font-size:34px;line-height:1.1;letter-spacing:-.04em;font-weight:900;margin:10px 0 0;">Pilih cara bayar sesuai kebutuhan instansi.</h2>
                    <p style="color:rgba(255,255,255,.68);line-height:1.7;margin:12px 0 0;">Semua paket tetap melalui alur resmi: daftar instansi, konfirmasi pembayaran, verifikasi admin, lalu token API diterbitkan.</p>

                    <div class="plan-grid">
                        @foreach ($plans as $code => $plan)
                            @php
                                $isFeatured = $code === 'package_600';
                                $price = (float) ($plan['price'] ?? 0);
                            @endphp
                            <article class="plan-card {{ $isFeatured ? 'is-featured' : '' }}">
                                <div>
                                    <h3>{{ $plan['name'] }}</h3>
                                    <p>{{ $plan['tagline'] ?? '' }}</p>
                                </div>
                                <div class="plan-price">
                                    @if ($price > 0)
                                        Rp {{ number_format($price, 0, ',', '.') }}
                                    @else
                                        Sesuai pemakaian
                                    @endif
                                </div>
                                <div class="plan-note">{{ $plan['quota_label'] ?? ($plan['days'] . ' hari akses') }}</div>
                                <ul class="check-list" style="margin-top:4px;">
                                    @foreach (($plan['features'] ?? []) as $feature)
                                        <li>{{ $feature }}</li>
                                    @endforeach
                                </ul>
                            </article>
                        @endforeach
                    </div>
                    <a href="#form-langganan" class="btn btn-white" style="width:100%;margin-top:24px;">Daftarkan Instansi</a>
                </div>
            </div>
        </section>

        <section class="section" style="padding-top:0;background:#f3fbf5;border-bottom:1px solid var(--line);">
            <div class="shell">
                <div class="section-head" style="margin-bottom:22px;">
                    <div class="mini-label">Hak akses setelah aktif</div>
                    <h2>Instansi tahu persis apa yang diterima.</h2>
                    <p>Setelah admin menyetujui pendaftaran, pemerintah atau instansi mendapatkan akses data agregat yang siap dipakai untuk pemantauan dan laporan kebijakan.</p>
                </div>
                <div class="access-grid">
                    <div class="access-card"><strong>Token API aktif</strong><span>Token 6 digit diterbitkan setelah pembayaran lunas dan data instansi diverifikasi admin.</span></div>
                    <div class="access-card"><strong>Data operasional wilayah</strong><span>Overview petani, lahan, aktivitas budidaya, penyakit tanaman, produktivitas, dan insight.</span></div>
                    <div class="access-card"><strong>Integrasi dashboard</strong><span>Instansi bisa menarik data ke command center, dashboard pangan, atau laporan internal.</span></div>
                </div>
            </div>
        </section>

        <section id="form-langganan" class="section">
            <div class="shell form-wrap">
                <div>
                    <div class="mini-label">Pendaftaran instansi</div>
                    <h2 style="font-size:42px;line-height:1.06;letter-spacing:-.05em;font-weight:900;margin:10px 0 14px;">Mulai akses data resmi P.A.D.I.</h2>
                    <p style="color:var(--muted);line-height:1.75;margin:0;">Lengkapi data kedinasan. Setelah formulir terkirim, sistem membuat kode registrasi dan mengarahkan Anda ke halaman billing WhatsApp.</p>
                    <div style="margin-top:22px;padding:18px;border-radius:22px;background:var(--green-50);border:1px solid var(--line);">
                        <strong style="display:block;color:var(--green-900);margin-bottom:6px;">Yang disiapkan setelah disetujui</strong>
                        <span style="color:var(--muted);font-size:13px;line-height:1.6;display:block;">Token API, masa berlaku akses, dokumentasi endpoint, dan contoh request siap pakai untuk tim teknis instansi.</span>
                    </div>
                </div>

                <div class="form-card">
                    @if ($errors->any())
                        <div class="error-box">
                            <strong style="display:block;margin-bottom:6px;">Harap periksa isian berikut:</strong>
                            <ul style="margin:0;padding-left:18px;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('government.subscribe') }}">
                        @csrf
                        <div class="field">
                            <label>Pilih Paket Akses</label>
                            <div class="plan-choice-grid">
                                @foreach ($plans as $code => $plan)
                                    @php
                                        $price = (float) ($plan['price'] ?? 0);
                                        $checked = old('plan_code', 'package_600') === $code;
                                    @endphp
                                    <label class="plan-choice">
                                        <input type="radio" name="plan_code" value="{{ $code }}" {{ $checked ? 'checked' : '' }} required>
                                        <span class="plan-choice-box">
                                            <span class="plan-choice-title">{{ $plan['name'] }}</span>
                                            <span class="plan-choice-price">
                                                @if ($price > 0)
                                                    Rp {{ number_format($price, 0, ',', '.') }}
                                                @else
                                                    Sesuai pemakaian
                                                @endif
                                            </span>
                                            <span class="plan-choice-caption">{{ $plan['billing_label'] ?? ($plan['days'] . ' hari') }}</span>
                                            <span class="plan-choice-caption">{{ $plan['quota_label'] ?? '' }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="field"><label>Nama Instansi / Dinas Pertanian</label><input type="text" name="agency_name" value="{{ old('agency_name') }}" required placeholder="Contoh: Dinas Pertanian Kab. Indramayu"></div>
                        <div class="input-grid">
                            <div class="field"><label>Email Resmi Instansi</label><input type="email" name="agency_email" value="{{ old('agency_email') }}" required placeholder="dinas@kab.go.id"></div>
                            <div class="field"><label>Nama PIC</label><input type="text" name="pic_name" value="{{ old('pic_name') }}" required placeholder="Nama penanggung jawab"></div>
                        </div>
                        <div class="field"><label>Nomor WhatsApp PIC</label><input type="text" name="pic_phone" value="{{ old('pic_phone') }}" required placeholder="081234567890"></div>
                        <div class="field"><label>Tujuan akses data</label><textarea name="purpose" rows="4" placeholder="Contoh: Dashboard ketahanan pangan daerah, pemetaan penyakit, dan mitigasi gagal panen.">{{ old('purpose') }}</textarea></div>

                        <div class="summary">
                            <div><strong style="display:block;">Alur setelah daftar</strong><span style="color:var(--muted);font-size:13px;">Billing WhatsApp -> pembayaran -> verifikasi admin -> token API aktif</span></div>
                            <div style="text-align:right;"><span style="display:block;color:var(--muted);font-size:12px;font-weight:800;">Paket utama</span><strong style="font-size:22px;color:var(--green-800);">Rp {{ number_format($planPrice, 0, ',', '.') }}</strong></div>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width:100%;border-radius:18px;padding:16px 18px;">Daftar dan lanjut ke billing WhatsApp</button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="shell" style="display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap;">
            <div><strong style="color:white;">P.A.D.I. Smart Farming</strong><div style="font-size:13px;margin-top:4px;">Government Data Access untuk pengambilan keputusan pangan.</div></div>
            <div style="display:flex;gap:18px;font-size:13px;font-weight:800;"><a href="{{ url('/') }}" style="color:#cbd5e1;">Beranda</a><a href="{{ route('government.docs') }}" style="color:#cbd5e1;">Dokumentasi API</a><a href="{{ url('/admin/login') }}" style="color:#cbd5e1;">Admin</a></div>
        </div>
    </footer>
</body>
</html>
