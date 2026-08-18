<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $profile['headline'] ?? 'Profil Usaha Pertanian dan Distribusi Beras' }} — {{ $profile['business_name'] }}">
    <meta name="robots" content="{{ $isPreview ? 'noindex, nofollow' : 'index, follow' }}">

    <title>{{ $profile['business_name'] }} &mdash; Profil Usaha Pertanian</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --brand-green: #166534;
            --brand-green-dark: #14532d;
            --brand-green-subtle: #f0fdf4;
            --brand-dark: #0f172a;
            --brand-slate: #334155;
            --brand-muted: #64748b;
            --brand-border: #e2e8f0;
            --brand-bg: #f8fafc;
            --brand-white: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
            background-color: var(--brand-bg);
            color: var(--brand-dark);
            margin: 0;
            padding: 0;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .site-container {
            max-width: 1180px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Top utility bar */
        .top-utility-bar {
            background-color: var(--brand-dark);
            color: #94a3b8;
            font-size: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #1e293b;
        }

        /* Navigation */
        .main-header {
            background-color: #ffffff;
            border-bottom: 1px solid var(--brand-border);
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .nav-inner {
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .brand-logo-wrap {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
            color: var(--brand-dark);
        }

        .brand-logo-img {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid var(--brand-border);
            flex-shrink: 0;
        }

        .brand-logo-fallback {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            background-color: var(--brand-green);
            color: #ffffff;
            font-weight: 800;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .brand-text-name {
            font-size: 17px;
            font-weight: 800;
            color: var(--brand-dark);
            letter-spacing: -0.02em;
            line-height: 1.2;
            margin: 0;
        }

        .brand-text-sub {
            font-size: 12px;
            color: var(--brand-muted);
            font-weight: 500;
            margin: 2px 0 0;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .nav-link-item {
            color: var(--brand-slate);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .nav-link-item:hover {
            color: var(--brand-green);
        }

        /* Buttons */
        .btn-green {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: var(--brand-green);
            color: #ffffff !important;
            font-size: 13px;
            font-weight: 700;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            border: 1px solid var(--brand-green);
            transition: all 0.15s ease;
            cursor: pointer;
        }

        .btn-green:hover {
            background-color: var(--brand-green-dark);
            border-color: var(--brand-green-dark);
        }

        .btn-whatsapp {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: #16a34a;
            color: #ffffff !important;
            font-size: 13px;
            font-weight: 700;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.15s ease;
            cursor: pointer;
        }

        .btn-whatsapp:hover {
            background-color: #15803d;
        }

        .btn-outline {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: #ffffff;
            color: var(--brand-dark) !important;
            font-size: 13px;
            font-weight: 700;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            border: 1px solid #cbd5e1;
            transition: all 0.15s ease;
        }

        .btn-outline:hover {
            background-color: #f1f5f9;
            border-color: #94a3b8;
        }

        /* Hero Banner */
        .hero-section {
            background-color: #ffffff;
            border-bottom: 1px solid var(--brand-border);
            padding: 48px 0;
        }

        .hero-layout {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 40px;
            align-items: center;
        }

        @media (max-width: 900px) {
            .hero-layout {
                grid-template-columns: 1fr;
                gap: 28px;
            }
        }

        .hero-banner-image {
            width: 100%;
            height: 320px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--brand-border);
            position: relative;
        }

        .hero-banner-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Product Cards */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }

        .product-card {
            background: #ffffff;
            border: 1px solid var(--brand-border);
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .product-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
            transform: translateY(-2px);
        }

        .product-image-box {
            height: 200px;
            background: #f1f5f9;
            position: relative;
            overflow: hidden;
        }

        .product-image-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-body {
            padding: 20px;
        }

        .product-title {
            font-size: 16px;
            font-weight: 800;
            color: var(--brand-dark);
            margin: 0 0 6px 0;
            line-height: 1.3;
        }

        .product-price {
            font-size: 20px;
            font-weight: 900;
            color: var(--brand-green);
            margin-bottom: 10px;
        }

        .product-price small {
            font-size: 13px;
            font-weight: 500;
            color: var(--brand-muted);
        }

        .product-description {
            font-size: 13px;
            color: var(--brand-muted);
            line-height: 1.6;
            margin: 0;
        }

        .product-footer {
            padding: 0 20px 20px;
        }

        /* Specification table */
        .data-table-card {
            background: #ffffff;
            border: 1px solid var(--brand-border);
            border-radius: 12px;
            overflow: hidden;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13px;
        }

        .data-table th {
            padding: 12px 18px;
            background: var(--brand-dark);
            color: #ffffff;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .data-table td {
            padding: 14px 18px;
            border-bottom: 1px solid #f1f5f9;
            color: var(--brand-slate);
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        /* Section Titles */
        .section-header {
            margin-bottom: 24px;
        }

        .section-tag {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--brand-green);
            margin-bottom: 4px;
        }

        .section-title {
            font-size: 22px;
            font-weight: 900;
            color: var(--brand-dark);
            letter-spacing: -0.02em;
            margin: 0;
        }

        /* Contact Box */
        .contact-box {
            background-color: #ffffff;
            border: 1px solid var(--brand-border);
            border-radius: 14px;
            padding: 36px;
        }
    </style>
</head>
<body>

    {{-- Preview Bar --}}
    @if ($isPreview)
        <div style="background-color:#166534; color:#ffffff; padding:10px 16px; font-size:13px; font-weight:700; text-align:center; position:sticky; top:0; z-index:999; display:flex; align-items:center; justify-content:center; gap:16px;">
            <span>Mode Preview Publikasi &mdash; Tampilan resmi website usaha tani</span>
            <a href="{{ route('farmer.website.index') }}" style="background:#ffffff; color:#166534; padding:4px 12px; border-radius:6px; font-size:12px; text-decoration:none; font-weight:700;">
                Kembali ke Panel
            </a>
        </div>
    @endif

    {{-- Utility Bar --}}
    <div class="top-utility-bar">
        <div class="site-container" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div style="display:flex; align-items:center; gap:16px;">
                <span>Profil Resmi Usaha Tani Terverifikasi</span>
                @if ($sections['show_location'] && !empty($location['address']))
                    <span>&bull;</span>
                    <span>Wilayah: {{ $location['address'] }}</span>
                @endif
            </div>
            @if ($sections['show_contact'] && !empty($contact['public_phone']))
                <div>
                    <span>Layanan Pelanggan: <strong>{{ $contact['public_phone'] }}</strong></span>
                </div>
            @endif
        </div>
    </div>

    {{-- Main Navbar --}}
    <header class="main-header">
        <div class="site-container">
            <div class="nav-inner">
                <a href="#hero" class="brand-logo-wrap">
                    @if ($profile['logo_url'])
                        <img src="{{ $profile['logo_url'] }}" alt="{{ $profile['business_name'] }}" class="brand-logo-img">
                    @else
                        <div class="brand-logo-fallback">
                            {{ substr($profile['business_name'], 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <h2 class="brand-text-name">{{ $profile['business_name'] }}</h2>
                        <p class="brand-text-sub">Usaha Pertanian & Pasokan Padi</p>
                    </div>
                </a>

                <nav class="nav-links">
                    @if ($sections['show_products'] && count($products) > 0)
                        <a href="#katalog" class="nav-link-item">Katalog Hasil Panen</a>
                    @endif
                    @if ($sections['show_harvests'] && count($harvests) > 0)
                        <a href="#riwayat" class="nav-link-item">Rekam Panen</a>
                    @endif
                    @if ($sections['show_gallery'] && count($gallery) > 0)
                        <a href="#galeri" class="nav-link-item">Dokumentasi Lahan</a>
                    @endif
                    @if ($sections['show_contact'] && $contact)
                        <a href="#kontak" class="btn-green" style="padding:8px 16px;">
                            Hubungi Usaha
                        </a>
                    @endif
                </nav>
            </div>
        </div>
    </header>

    {{-- Hero Section --}}
    <section id="hero" class="hero-section">
        <div class="site-container">
            <div class="hero-layout">
                <div>
                    @if ($profile['is_verified'])
                        <div style="display:inline-flex; align-items:center; gap:6px; background:#dcfce7; color:#166534; font-size:12px; font-weight:700; padding:4px 10px; border-radius:6px; margin-bottom:14px; border:1px solid #86efac;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                                <path d="m9 12 2 2 4-4"/>
                            </svg>
                            Terverifikasi P.A.D.I.
                        </div>
                    @endif


                    <h1 style="font-size:clamp(28px, 4vw, 40px); font-weight:900; color:#0f172a; letter-spacing:-0.03em; line-height:1.2; margin:0 0 12px 0;">
                        {{ $profile['business_name'] }}
                    </h1>

                    @if ($profile['headline'])
                        <p style="font-size:16px; font-weight:600; color:#166534; margin:0 0 14px 0;">
                            {{ $profile['headline'] }}
                        </p>
                    @endif

                    @if ($profile['description'])
                        <p style="font-size:14px; color:#475569; line-height:1.7; margin:0 0 24px 0;">
                            {{ $profile['description'] }}
                        </p>
                    @else
                        <p style="font-size:14px; color:#475569; line-height:1.7; margin:0 0 24px 0;">
                            Kami mengelola budidaya lahan pertanian padi secara terstruktur dan terstandar, menyediakan pasokan gabah dan beras berkualitas tinggi langsung dari sumber panen untuk kebutuhan distributor, pengecer, maupun konsumen langsung.
                        </p>
                    @endif

                    <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                        @if ($sections['show_contact'] && !empty($contact['whatsapp']))
                            <a href="{{ $contact['whatsapp'] }}" target="_blank" class="btn-whatsapp">
                                Pesan Langsung via WhatsApp
                            </a>
                        @endif
                        @if ($sections['show_products'] && count($products) > 0)
                            <a href="#katalog" class="btn-outline">
                                Lihat Daftar Produk
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Hero Banner Image --}}
                <div class="hero-banner-image">
                    <img src="{{ $profile['cover_image_url'] }}" alt="{{ $profile['business_name'] }}">
                </div>
            </div>
        </div>
    </section>

    {{-- Capacity Summary Strip --}}
    @if ($sections['show_productivity'] && $statistics)
        <div style="background:#ffffff; border-bottom:1px solid var(--brand-border); padding:24px 0;">
            <div class="site-container">
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px;">
                    <div style="background:#f8fafc; border:1px solid var(--brand-border); border-radius:10px; padding:16px 20px;">
                        <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.04em;">Total Luas Lahan</div>
                        <div style="font-size:24px; font-weight:900; color:#166534; margin:4px 0 0;">{{ $statistics['total_area_ha'] }} Ha</div>
                    </div>
                    <div style="background:#f8fafc; border:1px solid var(--brand-border); border-radius:10px; padding:16px 20px;">
                        <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.04em;">Pengalaman Budidaya</div>
                        <div style="font-size:24px; font-weight:900; color:#166534; margin:4px 0 0;">{{ $statistics['total_seasons'] }} Musim Tanam</div>
                    </div>
                    @if ($statistics['latest_productivity'])
                        <div style="background:#f8fafc; border:1px solid var(--brand-border); border-radius:10px; padding:16px 20px;">
                            <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.04em;">Rata-Rata Produktivitas</div>
                            <div style="font-size:24px; font-weight:900; color:#166534; margin:4px 0 0;">{{ $statistics['latest_productivity'] }} Ton / Ha</div>
                        </div>
                    @endif
                    <div style="background:#f8fafc; border:1px solid var(--brand-border); border-radius:10px; padding:16px 20px;">
                        <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.04em;">Kapasitas Pasokan</div>
                        <div style="font-size:18px; font-weight:800; color:#0f172a; margin:8px 0 0;">Siap Kirim Rutin</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Main Content Sections --}}
    <main class="site-container" style="padding-top:48px; padding-bottom:64px;" class="space-y-16">

        {{-- Section: Products --}}
        @if ($sections['show_products'] && count($products) > 0)
            <section id="katalog" style="margin-bottom:56px;">
                <div class="section-header" style="display:flex; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; gap:16px;">
                    <div>
                        <div class="section-tag">Katalog Komoditas</div>
                        <h2 class="section-title">Hasil Panen & Komoditas Siap Pasok</h2>
                    </div>
                    <span style="font-size:12px; font-weight:700; color:#166534; background:#dcfce7; padding:4px 12px; border-radius:6px;">
                        {{ count($products) }} Jenis Komoditas
                    </span>
                </div>

                <div class="product-grid">
                    @foreach ($products as $product)
                        <div class="product-card">
                            <div>
                                <div class="product-image-box">
                                    <img src="{{ $product['image_url'] }}" alt="{{ $product['commodity'] }}">
                                    <div style="position:absolute; top:10px; right:10px; background:#0f172a; color:#ffffff; font-size:11px; font-weight:700; padding:3px 8px; border-radius:4px;">
                                        Tersedia: {{ $product['quantity'] }} {{ $product['unit'] }}
                                    </div>
                                </div>

                                <div class="product-body">
                                    <h3 class="product-title">{{ $product['commodity'] }}</h3>
                                    
                                    @if ($product['price_per_unit'])
                                        <div class="product-price">
                                            Rp{{ number_format($product['price_per_unit'], 0, ',', '.') }}
                                            <small>/ {{ $product['unit'] }}</small>
                                        </div>
                                    @endif

                                    @if ($product['description'])
                                        <p class="product-description">{{ $product['description'] }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="product-footer">
                                @if ($product['sales_link'])
                                    <a href="{{ $product['sales_link'] }}" target="_blank" rel="nofollow noopener" class="btn-green" style="width:100%;">
                                        Pesan Komoditas Ini
                                    </a>
                                @elseif ($sections['show_contact'] && !empty($contact['whatsapp']))
                                    @php
                                        $waMsg = urlencode("Halo {$profile['business_name']}, saya berminat memesan {$product['commodity']}. Mohon informasi stok dan pengirimannya.");
                                        $waUrl = $contact['whatsapp'] . (str_contains($contact['whatsapp'], '?') ? '&' : '?') . "text={$waMsg}";
                                    @endphp
                                    <a href="{{ $waUrl }}" target="_blank" class="btn-whatsapp" style="width:100%;">
                                        Tanya Ketersediaan via WA
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Section: Harvest Log --}}
        @if ($sections['show_harvests'] && count($harvests) > 0)
            <section id="riwayat" style="margin-bottom:56px;">
                <div class="section-header">
                    <div class="section-tag">Transparansi Produksi</div>
                    <h2 class="section-title">Rekam Jejak Hasil Panen</h2>
                </div>

                <div class="data-table-card">
                    <div style="overflow-x:auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Periode Panen</th>
                                    <th>Lahan Sawah</th>
                                    <th>Varietas Padi</th>
                                    <th style="text-align:right;">Volume Panen</th>
                                    <th style="text-align:center;">Grade Kualitas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($harvests as $harvest)
                                    <tr>
                                        <td style="font-weight:700; color:#0f172a;">{{ \Carbon\Carbon::parse($harvest['harvest_date'])->translatedFormat('F Y') }}</td>
                                        <td>{{ $harvest['farm_name'] ?? 'Lahan Utama' }}</td>
                                        <td style="font-weight:600; color:#166534;">{{ $harvest['variety_name'] ?? 'Varietas Unggul' }}</td>
                                        <td style="text-align:right; font-weight:800; color:#0f172a;">{{ number_format($harvest['quantity'], 1, ',', '.') }} {{ $harvest['unit'] }}</td>
                                        <td style="text-align:center;">
                                            <span style="font-size:11px; font-weight:800; padding:3px 10px; border-radius:6px; background:#dcfce7; color:#166534;">
                                                {{ $harvest['quality_grade'] ?? 'Grade A' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif

        {{-- Section: Gallery --}}
        @if ($sections['show_gallery'] && count($gallery) > 0)
            <section id="galeri" style="margin-bottom:56px;">
                <div class="section-header">
                    <div class="section-tag">Dokumentasi Lapangan</div>
                    <h2 class="section-title">Galeri Sawah & Pengeringan Gabah</h2>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:18px;">
                    @foreach ($gallery as $item)
                        @php
                            $imgSrc = is_array($item) ? $item['image_url'] : asset('storage/' . $item->image_path);
                            $cap = is_array($item) ? ($item['caption'] ?? null) : ($item->caption ?? null);
                        @endphp
                        <div style="background:#ffffff; border:1px solid var(--brand-border); border-radius:10px; overflow:hidden; aspect-ratio:4/3; position:relative;">
                            <img src="{{ $imgSrc }}" alt="{{ $cap ?? 'Galeri' }}" style="width:100%; height:100%; object-fit:cover;">
                            @if ($cap)
                                <div style="position:absolute; bottom:0; left:0; right:0; background:rgba(15,23,42,0.85); color:#ffffff; font-size:11px; font-weight:600; padding:8px 12px;">
                                    {{ $cap }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Section: Contact & Order Information --}}
        @if ($sections['show_contact'] && $contact)
            <section id="kontak">
                <div class="contact-box">
                    <div style="max-width:680px; margin:0 auto; text-align:center;">
                        <div class="section-tag">Hubungi Kami</div>
                        <h2 style="font-size:24px; font-weight:900; color:#0f172a; margin:0 0 10px 0;">
                            Pemesanan Komoditas & Kerja Sama Pasokan
                        </h2>
                        <p style="font-size:14px; color:#64748b; line-height:1.6; margin:0 0 28px 0;">
                            Silakan hubungi kami langsung untuk informasi ketersediaan stok gabah/beras, negosiasi harga partai besar, atau permintaan sampel.
                        </p>

                        <div style="display:flex; align-items:center; justify-content:center; gap:12px; flex-wrap:wrap; margin-bottom:24px;">
                            @if (!empty($contact['whatsapp']))
                                <a href="{{ $contact['whatsapp'] }}" target="_blank" class="btn-whatsapp" style="padding:12px 24px; font-size:14px;">
                                    WhatsApp Langsung
                                </a>
                            @endif
                            @if (!empty($contact['public_phone']))
                                <a href="tel:{{ $contact['public_phone'] }}" class="btn-outline" style="padding:12px 24px; font-size:14px;">
                                    Telepon: {{ $contact['public_phone'] }}
                                </a>
                            @endif
                            @if (!empty($contact['public_email']))
                                <a href="mailto:{{ $contact['public_email'] }}" class="btn-outline" style="padding:12px 24px; font-size:14px;">
                                    Kirim Email
                                </a>
                            @endif
                        </div>

                        @if ($sections['show_location'] && !empty($location['address']))
                            <div style="font-size:13px; color:#64748b; border-top:1px solid var(--brand-border); padding-top:18px;">
                                📍 <strong>Alamat Usaha:</strong> {{ $location['address'] }}
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        @endif

    </main>

    {{-- Footer --}}
    <footer style="background-color:#0f172a; border-top:1px solid #1e293b; color:#94a3b8; font-size:12px; padding:32px 0; text-align:center;">
        <div class="site-container">
            <div style="margin-bottom:8px; font-weight:700; color:#ffffff; font-size:14px;">
                {{ $profile['business_name'] }}
            </div>
            <p style="margin:0; color:#64748b;">
                Halaman profil usaha pertanian resmi yang terdaftar pada ekosistem P.A.D.I.
            </p>
            <div style="margin-top:12px; font-size:11px; color:#475569;">
                &copy; {{ date('Y') }} {{ $profile['business_name'] }}. Seluruh hak cipta dilindungi.
            </div>
        </div>
    </footer>

</body>
</html>
