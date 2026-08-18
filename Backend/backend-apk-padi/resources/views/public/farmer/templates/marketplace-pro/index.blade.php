<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="{{ $profile['headline'] ?? 'Katalog Pasokan Hasil Panen Komoditas Beras dan Gabah' }} — {{ $profile['business_name'] }}">
    <meta name="robots" content="{{ $isPreview ? 'noindex, nofollow' : 'index, follow' }}">

    <title>{{ $profile['business_name'] }} &mdash; Etalase Pasokan Padi</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Fonts & Icons -->
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary: #1a5c3a;
            --primary-dark: #0f3d26;
            --primary-light: #e8f5ed;
            --accent: #c8a951;
            --accent-light: #f5edd7;
            --dark: #1a1a1a;
            --gray-800: #2d3748;
            --gray-600: #4a5568;
            --gray-400: #a0aec0;
            --gray-200: #e2e8f0;
            --gray-100: #f7fafc;
            --white: #ffffff;
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px rgba(0, 0, 0, 0.15);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--gray-100);
            color: var(--gray-800);
            line-height: 1.7;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        .container {
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* ===== Preview Bar ===== */
        .preview-bar {
            background: linear-gradient(135deg, #c8a951 0%, #b8983d 100%);
            color: #1a1a1a;
            padding: 12px 24px;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .preview-bar .btn-dashboard {
            background: #1a1a1a;
            color: #c8a951;
            padding: 8px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            font-size: 12px;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .preview-bar .btn-dashboard:hover {
            background: #333;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        /* ===== Top Bar ===== */
        .top-bar {
            background: var(--dark);
            color: rgba(255, 255, 255, 0.8);
            font-size: 12px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .top-bar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .top-bar-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .top-bar-item i {
            color: var(--accent);
            font-size: 14px;
        }

        /* ===== Navbar ===== */
        .navbar {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-bottom: 2px solid var(--gray-200);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: var(--transition);
        }

        .navbar.scrolled {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-bottom-color: var(--accent);
        }

        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 76px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
        }

        .brand-logo {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-md);
            overflow: hidden;
            border: 2px solid var(--primary-light);
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 22px;
            color: white;
            flex-shrink: 0;
            box-shadow: 0 4px 8px rgba(26, 92, 58, 0.2);
        }

        .brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .brand-text {
            display: flex;
            flex-direction: column;
        }

        .brand-name {
            font-family: 'Sora', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
            letter-spacing: -0.5px;
            line-height: 1.2;
        }

        .brand-tagline {
            font-size: 11px;
            color: var(--primary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 24px;
            list-style: none;
        }

        .nav-link {
            color: var(--gray-600);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            position: relative;
            padding: 8px 0;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: var(--accent);
            transition: var(--transition);
            border-radius: 2px;
        }

        .nav-link:hover {
            color: var(--primary);
        }

        .nav-link:hover::after {
            width: 100%;
        }

        /* ===== Buttons ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transition: var(--transition);
        }

        .btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-whatsapp {
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
        }

        .btn-whatsapp:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 211, 102, 0.4);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(26, 92, 58, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26, 92, 58, 0.4);
        }

        .btn-outline {
            background: white;
            color: var(--gray-800);
            border: 2px solid var(--gray-200);
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-light);
        }

        /* ===== Supplier Card ===== */
        .supplier-card {
            background: white;
            border-radius: var(--radius-xl);
            overflow: hidden;
            margin: 32px 0 48px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
            position: relative;
        }

        .supplier-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 50%, var(--primary) 100%);
            z-index: 2;
        }

        .cover-banner {
            height: 280px;
            background: var(--dark);
            position: relative;
            overflow: hidden;
        }

        .cover-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .supplier-card:hover .cover-banner img {
            transform: scale(1.05);
        }

        .cover-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.7) 0%, transparent 100%);
            padding: 40px 32px 20px;
            color: white;
        }

        .supplier-info {
            padding: 32px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 32px;
            align-items: start;
        }

        @media (max-width: 768px) {
            .supplier-info {
                grid-template-columns: 1fr;
                padding: 24px;
            }
        }

        .verified-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--primary-light);
            color: var(--primary);
            font-size: 12px;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 50px;
            margin-bottom: 16px;
            border: 1px solid rgba(26, 92, 58, 0.2);
        }

        .verified-badge i {
            font-size: 14px;
        }

        .supplier-name {
            font-family: 'Sora', sans-serif;
            font-size: 28px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .supplier-headline {
            font-size: 16px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 12px;
        }

        .supplier-desc {
            font-size: 14px;
            color: var(--gray-600);
            line-height: 1.7;
            max-width: 700px;
        }

        .capacity-card {
            background: var(--gray-100);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-md);
            padding: 24px;
            min-width: 200px;
            text-align: center;
        }

        .capacity-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .capacity-value {
            font-family: 'Sora', sans-serif;
            font-size: 32px;
            font-weight: 800;
            color: var(--primary);
            line-height: 1;
            margin-bottom: 4px;
        }

        .capacity-sub {
            font-size: 12px;
            color: var(--gray-400);
        }

        /* ===== Products Grid ===== */
        .section-header {
            margin-bottom: 32px;
        }

        .section-title {
            font-family: 'Sora', sans-serif;
            font-size: 24px;
            font-weight: 800;
            color: var(--dark);
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }

        .section-subtitle {
            font-size: 14px;
            color: var(--gray-600);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 24px;
            margin-bottom: 48px;
        }

        .product-card {
            background: white;
            border-radius: var(--radius-lg);
            overflow: hidden;
            border: 1px solid var(--gray-200);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .product-card:hover {
            box-shadow: var(--shadow-xl);
            transform: translateY(-4px);
            border-color: var(--accent);
        }

        .product-image {
            height: 200px;
            position: relative;
            overflow: hidden;
            background: var(--gray-100);
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .product-card:hover .product-image img {
            transform: scale(1.1);
        }

        .stock-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: var(--dark);
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 50px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 2;
        }

        .product-content {
            padding: 24px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .product-name {
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .product-price {
            font-size: 22px;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 12px;
        }

        .product-price span {
            font-size: 13px;
            font-weight: 500;
            color: var(--gray-400);
        }

        .product-desc {
            font-size: 13px;
            color: var(--gray-600);
            line-height: 1.6;
            margin-bottom: 20px;
            flex: 1;
        }

        .product-action {
            display: flex;
            gap: 12px;
        }

        .product-action .btn {
            flex: 1;
            justify-content: center;
            padding: 12px 20px;
            font-size: 13px;
        }

        /* ===== Contact Section ===== */
        .contact-card {
            background: white;
            border-radius: var(--radius-xl);
            padding: 48px;
            text-align: center;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-200);
            position: relative;
            overflow: hidden;
        }

        .contact-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 100%);
        }

        .contact-title {
            font-family: 'Sora', sans-serif;
            font-size: 28px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 12px;
        }

        .contact-desc {
            font-size: 14px;
            color: var(--gray-600);
            margin-bottom: 32px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .contact-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* ===== Footer ===== */
        .footer {
            background: var(--dark);
            color: rgba(255, 255, 255, 0.7);
            padding: 40px 0;
            font-size: 13px;
            margin-top: 64px;
        }

        .footer .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .footer-brand {
            font-family: 'Sora', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: white;
            margin-bottom: 4px;
        }

        .footer-copy {
            color: rgba(255, 255, 255, 0.4);
            font-size: 12px;
        }

        /* ===== Responsive ===== */
        @media (max-width: 768px) {
            .navbar .container {
                height: auto;
                padding: 16px 24px;
                flex-direction: column;
                gap: 12px;
            }

            .nav-menu {
                flex-wrap: wrap;
                justify-content: center;
                gap: 16px;
            }

            .products-grid {
                grid-template-columns: 1fr;
            }

            .contact-card {
                padding: 32px 24px;
            }

            .contact-title {
                font-size: 24px;
            }

            .supplier-name {
                font-size: 24px;
            }

            .cover-banner {
                height: 200px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 16px;
            }

            .hero-actions {
                flex-direction: column;
            }

            .hero-actions .btn {
                width: 100%;
                justify-content: center;
            }

            .footer .container {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>

<body>

    {{-- Preview Bar --}}
    @if ($isPreview)
        <div class="preview-bar">
            <span><i class="fas fa-eye"></i> Mode Preview - Tampilan Publik Etalase</span>
            <a href="{{ route('farmer.website.index') }}" class="btn-dashboard">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    @endif

    {{-- Top Bar --}}
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-item">
                <i class="fas fa-certificate"></i>
                <span>Pusat Pasokan Komoditas Padi Resmi</span>
            </div>
            @if ($sections['show_location'] && !empty($location['address']))
                <div class="top-bar-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>{{ $location['address'] }}</span>
                </div>
            @endif
            @if ($sections['show_contact'] && !empty($contact['public_phone']))
                <div class="top-bar-item">
                    <i class="fas fa-headset"></i>
                    <span>Sales: {{ $contact['public_phone'] }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Navbar --}}
    <nav class="navbar" id="navbar">
        <div class="container">
            <a href="#hero" class="brand">
                @if ($profile['logo_url'])
                    <div class="brand-logo">
                        <img src="{{ $profile['logo_url'] }}" alt="{{ $profile['business_name'] }}">
                    </div>
                @else
                    <div class="brand-logo">
                        {{ strtoupper(substr($profile['business_name'], 0, 1)) }}
                    </div>
                @endif
                <div class="brand-text">
                    <span class="brand-name">{{ $profile['business_name'] }}</span>
                    <span class="brand-tagline">Etalase Pasokan Padi</span>
                </div>
            </a>

            <ul class="nav-menu">
                @if ($sections['show_products'] && count($products) > 0)
                    <li><a href="#katalog" class="nav-link"><i class="fas fa-box"></i> Komoditas</a></li>
                @endif
                @if ($sections['show_harvests'] && count($harvests) > 0)
                    <li><a href="#riwayat" class="nav-link"><i class="fas fa-history"></i> Riwayat Panen</a></li>
                @endif
                @if ($sections['show_gallery'] && count($gallery) > 0)
                    <li><a href="#galeri" class="nav-link"><i class="fas fa-images"></i> Galeri</a></li>
                @endif
                @if ($sections['show_contact'] && $contact)
                    <li><a href="#kontak" class="btn btn-whatsapp" style="padding:10px 20px; font-size:13px;">
                            <i class="fab fa-whatsapp"></i> Hubungi
                        </a></li>
                @endif
            </ul>
        </div>
    </nav>

    <main class="container" style="padding-bottom:0;">

        {{-- Supplier Hero Card --}}
        <div id="hero" class="supplier-card">
            <div class="cover-banner">
                <img src="{{ $profile['cover_image_url'] }}" alt="{{ $profile['business_name'] }}">
                <div class="cover-overlay">
                    <span style="font-size:14px; font-weight:600;">
                        <i class="fas fa-leaf"></i> Pasokan Langsung dari Lahan
                    </span>
                </div>
            </div>

            <div class="supplier-info">
                <div>
                    <div class="verified-badge">
                        <i class="fas fa-shield-alt"></i> Pemasok Terverifikasi
                    </div>

                    <h1 class="supplier-name">{{ $profile['business_name'] }}</h1>

                    @if ($profile['headline'])
                        <p class="supplier-headline">{{ $profile['headline'] }}</p>
                    @endif

                    @if ($profile['description'])
                        <p class="supplier-desc">{{ $profile['description'] }}</p>
                    @endif

                    @if ($sections['show_location'] && !empty($location['address']))
                        <div style="margin-top:16px; font-size:13px; color:var(--gray-600);">
                            <i class="fas fa-location-dot" style="color:var(--primary);"></i>
                            {{ $location['address'] }}
                        </div>
                    @endif
                </div>

                @if ($sections['show_productivity'] && $statistics)
                    <div class="capacity-card">
                        <div class="capacity-label">Kapasitas Lahan</div>
                        <div class="capacity-value">{{ $statistics['total_area_ha'] }} <small
                                style="font-size:16px;">Ha</small></div>
                        <div class="capacity-sub">{{ $statistics['total_seasons'] }} Musim Terdata</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Products Grid --}}
        @if ($sections['show_products'] && count($products) > 0)
            <section id="katalog" style="margin-bottom:48px;">
                <div class="section-header">
                    <h2 class="section-title">Daftar Hasil Panen Siap Pasok</h2>
                    <p class="section-subtitle">Kuantitas dan harga transparan langsung dari gudang / sawah petani.</p>
                </div>

                <div class="products-grid">
                    @foreach ($products as $product)
                        <div class="product-card">
                            <div class="product-image">
                                <img src="{{ $product['image_url'] }}" alt="{{ $product['commodity'] }}">
                                <div class="stock-badge">
                                    <i class="fas fa-box"></i> {{ $product['quantity'] }} {{ $product['unit'] }}
                                </div>
                            </div>

                            <div class="product-content">
                                <h3 class="product-name">{{ $product['commodity'] }}</h3>

                                @if ($product['price_per_unit'])
                                    <div class="product-price">
                                        Rp {{ number_format($product['price_per_unit'], 0, ',', '.') }}
                                        <span>/ {{ $product['unit'] }}</span>
                                    </div>
                                @endif

                                @if ($product['description'])
                                    <p class="product-desc">{{ $product['description'] }}</p>
                                @endif

                                <div class="product-action">
                                    @if ($product['sales_link'])
                                        <a href="{{ $product['sales_link'] }}" target="_blank" rel="nofollow"
                                            class="btn btn-primary">
                                            <i class="fas fa-shopping-cart"></i> Beli
                                        </a>
                                    @elseif ($sections['show_contact'] && !empty($contact['whatsapp']))
                                        @php
                                            $waMsg = urlencode("Halo {$profile['business_name']}, saya ingin menanyakan ketersediaan {$product['commodity']}.");
                                            $waUrl = $contact['whatsapp'] . (str_contains($contact['whatsapp'], '?') ? '&' : '?') . "text={$waMsg}";
                                        @endphp
                                        <a href="{{ $waUrl }}" target="_blank" class="btn btn-whatsapp">
                                            <i class="fab fa-whatsapp"></i> Pesan
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Harvest History (Optional) --}}
        @if ($sections['show_harvests'] && count($harvests) > 0)
            <section id="riwayat" style="margin-bottom:48px;">
                <div class="section-header">
                    <h2 class="section-title">Riwayat Panen Terbaru</h2>
                    <p class="section-subtitle">Transparansi data produksi untuk kepercayaan pembeli.</p>
                </div>

                <div
                    style="background:white; border-radius:var(--radius-lg); overflow:hidden; box-shadow:var(--shadow-sm); border:1px solid var(--gray-200);">
                    <div style="overflow-x:auto;">
                        <table style="width:100%; border-collapse:collapse; font-size:13px;">
                            <thead>
                                <tr style="background:var(--dark); color:white;">
                                    <th style="padding:14px 18px; text-align:left;">Periode</th>
                                    <th style="padding:14px 18px; text-align:left;">Lahan</th>
                                    <th style="padding:14px 18px; text-align:left;">Varietas</th>
                                    <th style="padding:14px 18px; text-align:right;">Volume</th>
                                    <th style="padding:14px 18px; text-align:center;">Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($harvests as $harvest)
                                    <tr style="border-bottom:1px solid var(--gray-100);">
                                        <td style="padding:14px 18px; font-weight:600;">
                                            {{ \Carbon\Carbon::parse($harvest['harvest_date'])->translatedFormat('F Y') }}</td>
                                        <td style="padding:14px 18px;">{{ $harvest['farm_name'] ?? 'Lahan Utama' }}</td>
                                        <td style="padding:14px 18px; color:var(--primary); font-weight:600;">
                                            {{ $harvest['variety_name'] ?? 'Varietas Padi' }}</td>
                                        <td style="padding:14px 18px; text-align:right; font-weight:700;">
                                            {{ number_format($harvest['quantity'], 1, ',', '.') }} {{ $harvest['unit'] }}</td>
                                        <td style="padding:14px 18px; text-align:center;">
                                            <span
                                                style="display:inline-block; padding:4px 12px; border-radius:50px; font-size:11px; font-weight:700; background:#fef3c7; color:#92400e;">
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

        {{-- Gallery (Optional) --}}
        @if ($sections['show_gallery'] && count($gallery) > 0)
            <section id="galeri" style="margin-bottom:48px;">
                <div class="section-header">
                    <h2 class="section-title">Dokumentasi Lahan</h2>
                    <p class="section-subtitle">Bukti nyata kualitas dan proses budidaya.</p>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:20px;">
                    @foreach ($gallery as $item)
                        @php
                            $imgSrc = is_array($item) ? $item['image_url'] : asset('storage/' . $item->image_path);
                            $cap = is_array($item) ? ($item['caption'] ?? '') : ($item->caption ?? '');
                        @endphp
                        <div style="position:relative; border-radius:var(--radius-md); overflow:hidden; aspect-ratio:4/3; cursor:pointer; transition:var(--transition);"
                            onmouseover="this.querySelector('.gallery-caption').style.opacity='1'"
                            onmouseout="this.querySelector('.gallery-caption').style.opacity='0'">
                            <img src="{{ $imgSrc }}" alt="{{ $cap ?: 'Galeri' }}"
                                style="width:100%; height:100%; object-fit:cover;">
                            @if ($cap)
                                <div class="gallery-caption"
                                    style="position:absolute; bottom:0; left:0; right:0; background:linear-gradient(to top, rgba(0,0,0,0.8), transparent); color:white; font-size:12px; padding:30px 16px 12px; opacity:0; transition:var(--transition);">
                                    {{ $cap }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Contact Section --}}
        @if ($sections['show_contact'] && $contact)
            <section id="kontak" style="margin-bottom:0;">
                <div class="contact-card">
                    <h2 class="contact-title">Kontak Langsung Pemasok</h2>
                    <p class="contact-desc">
                        Hubungi kami untuk negosiasi kuantitas besar, sampel beras, atau jadwal pengiriman rutin.
                    </p>

                    <div class="contact-actions">
                        @if (!empty($contact['whatsapp']))
                            <a href="{{ $contact['whatsapp'] }}" target="_blank" class="btn btn-whatsapp"
                                style="padding:14px 32px; font-size:15px;">
                                <i class="fab fa-whatsapp"></i> WhatsApp Langsung
                            </a>
                        @endif
                        @if (!empty($contact['public_phone']))
                            <a href="tel:{{ $contact['public_phone'] }}" class="btn btn-outline"
                                style="padding:14px 32px; font-size:15px;">
                                <i class="fas fa-phone"></i> {{ $contact['public_phone'] }}
                            </a>
                        @endif
                        @if (!empty($contact['public_email']))
                            <a href="mailto:{{ $contact['public_email'] }}" class="btn btn-outline"
                                style="padding:14px 32px; font-size:15px;">
                                <i class="fas fa-envelope"></i> Email
                            </a>
                        @endif
                    </div>
                </div>
            </section>
        @endif

    </main>

    {{-- Footer --}}
    <footer class="footer">
        <div class="container">
            <div>
                <div class="footer-brand">{{ $profile['business_name'] }}</div>
                <div style="color:rgba(255,255,255,0.5); font-size:12px;">
                    Etalase Resmi Komoditas Pertanian P.A.D.I.
                </div>
            </div>
            <div class="footer-copy">
                &copy; {{ date('Y') }} {{ $profile['business_name'] }}. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function () {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>

</body>

</html>